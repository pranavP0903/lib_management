<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Member;
use App\Models\Circulation;
use App\Models\Fine;
use App\Models\BookCopy;
use App\Models\LibrarySetting;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /* =======================
       DASHBOARD
    ======================== */
    public function index()
    {
        $monthly_stats = [
            'books_issued' =>
                Circulation::whereMonth('issue_date', now()->month)->count(),

            'books_returned' =>
                Circulation::whereNotNull('return_date')
                    ->whereMonth('return_date', now()->month)
                    ->count(),

            'overdue_cases' =>
                Circulation::whereNull('return_date')
                    ->where('due_date', '<', now())
                    ->count(),

            // ✅ FIXED: fine_amount
            'fines_collected' =>
                Fine::where('status', 'PAID')
                    ->whereMonth('created_at', now()->month)
                    ->sum('fine_amount'),
        ];

        $quick_stats = [
            'total_books' => Book::count(),
            'active_members' => Member::where('status', 'ACTIVE')->count(),
            'monthly_circulation' =>
                Circulation::whereMonth('issue_date', now()->month)->count(),
            'new_books' =>
                Book::whereMonth('created_at', now()->month)->count(),
        ];

        $categories = Book::distinct()->pluck('category');

        return view('reports.index', compact(
            'monthly_stats',
            'quick_stats',
            'categories'
        ));
    }

    /* =======================
       CIRCULATION REPORT
    ======================== */
    public function circulation(Request $request)
    {
        $start = $request->start_date ?? now()->startOfMonth();
        $end   = $request->end_date ?? now()->endOfMonth();

        $circulations = Circulation::with(['member', 'copy.book'])
            ->whereBetween('issue_date', [$start, $end])
            ->when($request->member_type, function ($q) use ($request) {
                $q->whereHas('member', function ($m) use ($request) {
                    $m->where('member_type', $request->member_type);
                });
            })
            ->orderByDesc('issue_date')
            ->paginate(50);

        $items = collect($circulations->items());

        $summary = [
            'total_issues' => $circulations->total(),
            'total_returns' => $items->whereNotNull('return_date')->count(),
            'unique_members' => $items->unique('member_id')->count(),
            'unique_books' => $items
                ->map(fn ($c) => optional($c->copy)->book_id)
                ->unique()
                ->count(),
        ];

        return view('reports.circulation', [
            'circulations' => $circulations,
            'summary' => $summary,
            'stats' => $this->calculateCirculationStats($items),
            'top_books' => $this->getTopBooks($start, $end),
            'top_members' => $this->getTopMembers($start, $end),
        ]);
    }

    /* =======================
       OVERDUE REPORT
    ======================== */
    public function overdue()
    {
        $records = Circulation::with(['member', 'copy.book', 'fines'])
            ->whereNull('return_date')
            ->where('due_date', '<', now())
            ->get();

        $summary = [
            'total_overdue' => $records->count(),
            'total_fines' => $records->sum('calculated_fine'),
            'avg_overdue_days' =>
                $records->avg('overdue_days')
                    ? round($records->avg('overdue_days'), 1)
                    : 0,
            'affected_members' =>
                $records->unique('member_id')->count(),
        ];

        return view('reports.overdue', [
            'overdue_records' => $records,
            'summary' => $summary,
            'analysis' => $this->analyzeOverdueReport($records),
            'repeat_offenders' => $this->getRepeatOffenders($records),
            'fine_rate' => LibrarySetting::getValue('FINE_PER_DAY', 5),
        ]);
    }

    /* =======================
       MEMBER ACTIVITY REPORT
    ======================== */
    public function memberActivity(Request $request)
    {
        $start = $request->start_date ?? now()->subYear();
        $end   = $request->end_date ?? now();

        $members = Member::withCount([
            'circulations as total_borrowings' =>
                fn ($q) => $q->whereBetween('issue_date', [$start, $end]),

            'circulations as overdue_count' =>
                fn ($q) => $q->whereNull('return_date')
                    ->where('due_date', '<', now()),
        ])
        // ✅ FIXED: fine_amount
        ->withSum('fines as fines_paid', 'fine_amount')
        ->paginate(30);

        $items = collect($members->items());

        $summary = [
            'total_members' => $members->total(),
            'active_members' => $items->where('status', 'ACTIVE')->count(),
            'total_loans' => $items->sum('total_borrowings'),
            'avg_loans_per_member' =>
                $items->avg('total_borrowings')
                    ? round($items->avg('total_borrowings'), 1)
                    : 0,
        ];

        return view('reports.member-activity', [
            'members' => $members,
            'summary' => $summary,
            'type_stats' => $this->calculateMemberTypeStats($items),
            'top_performers' => $this->getTopPerformers($start, $end),
            'inactive_members' => $this->getInactiveMembers(),
        ]);
    }

    /* =======================
       INVENTORY REPORT
    ======================== */
    public function inventory()
    {
        $inventory = Book::leftJoin('book_copies', 'books.book_id', '=', 'book_copies.book_id')
            ->selectRaw('
                books.*,
                COUNT(book_copies.copy_id) as total_copies,
                SUM(book_copies.status = "AVAILABLE") as available_copies,
                SUM(book_copies.status = "ISSUED") as issued_copies,
                SUM(book_copies.status = "RESERVED") as reserved_copies,
                SUM(book_copies.status = "LOST") as lost_copies
            ')
            ->groupBy('books.book_id')
            ->paginate(30);

        return view('reports.inventory', [
            'inventory' => $inventory,
            'summary' => [
                'total_books' => Book::count(),
                'total_copies' => BookCopy::count(),
                'available_copies' =>
                    BookCopy::where('status', 'AVAILABLE')->count(),
            ],
            'categories' => Book::distinct()->pluck('category'),
        ]);
    }

    /* =======================
       HELPERS
    ======================== */
    private function calculateCirculationStats($items)
    {
        return [
            'student_issues' =>
                $items->where('member.member_type', 'STUDENT')->count(),
            'faculty_issues' =>
                $items->where('member.member_type', 'FACULTY')->count(),
        ];
    }

    private function getTopBooks($start, $end)
    {
        return Book::leftJoin('book_copies', 'books.book_id', '=', 'book_copies.book_id')
            ->leftJoin('circulation', 'book_copies.copy_id', '=', 'circulation.copy_id')
            ->whereBetween('circulation.issue_date', [$start, $end])
            ->selectRaw('books.*, COUNT(circulation.id) as issue_count')
            ->groupBy('books.book_id')
            ->orderByDesc('issue_count')
            ->limit(10)
            ->get();
    }

    private function getTopMembers($start, $end)
    {
        return Member::leftJoin('circulation', 'members.member_id', '=', 'circulation.member_id')
            ->whereBetween('circulation.issue_date', [$start, $end])
            ->selectRaw('members.*, COUNT(circulation.id) as issue_count')
            ->groupBy('members.member_id')
            ->orderByDesc('issue_count')
            ->limit(10)
            ->get();
    }

    private function analyzeOverdueReport($records)
    {
        return [
            'total' => $records->count(),
            'students' =>
                $records->where('member.member_type', 'STUDENT')->count(),
            'faculty' =>
                $records->where('member.member_type', 'FACULTY')->count(),
        ];
    }

    private function getRepeatOffenders($records)
    {
        return $records
            ->groupBy('member_id')
            ->map(fn ($g) => [
                'member' => $g->first()->member,
                'count' => $g->count(),
            ])
            ->sortByDesc('count')
            ->take(10)
            ->values();
    }

    private function calculateMemberTypeStats($members)
    {
        return [
            'students' => $members->where('member_type', 'STUDENT')->count(),
            'faculty'  => $members->where('member_type', 'FACULTY')->count(),
        ];
    }

    private function getTopPerformers($start, $end)
    {
        return Member::leftJoin('circulation', 'members.member_id', '=', 'circulation.member_id')
            ->whereBetween('circulation.issue_date', [$start, $end])
            ->whereNotNull('circulation.return_date')
            ->selectRaw('members.*, COUNT(circulation.id) as total_loans')
            ->groupBy('members.member_id')
            ->orderByDesc('total_loans')
            ->limit(10)
            ->get();
    }

    private function getInactiveMembers()
    {
        return Member::leftJoin('circulation', 'members.member_id', '=', 'circulation.member_id')
            ->groupBy('members.member_id')
            ->havingRaw('MAX(circulation.issue_date) IS NULL')
            ->limit(20)
            ->get();
    }
}
