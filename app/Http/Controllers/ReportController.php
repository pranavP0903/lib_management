<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Member;
use App\Models\Circulation;
use App\Models\Fine;
use App\Models\BookCopy;
use App\Models\LibrarySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
    $total = $items->count();

    $studentItems = $items->where('member.member_type', 'STUDENT');
    $facultyItems = $items->where('member.member_type', 'FACULTY');

    $studentIssues = $studentItems->count();
    $facultyIssues = $facultyItems->count();

    return [
        // Counts
        'student_issues' => $studentIssues,
        'faculty_issues' => $facultyIssues,

        // Percentages (SAFE)
        'student_percentage' =>
            $total > 0 ? round(($studentIssues / $total) * 100, 1) : 0,

        'faculty_percentage' =>
            $total > 0 ? round(($facultyIssues / $total) * 100, 1) : 0,

        // Average durations
        'student_avg_duration' =>
            $studentItems->avg('loan_duration')
                ? round($studentItems->avg('loan_duration'), 1)
                : 0,

        'faculty_avg_duration' =>
            $facultyItems->avg('loan_duration')
                ? round($facultyItems->avg('loan_duration'), 1)
                : 0,
    ];
}


    private function getTopBooks(Carbon $start, Carbon $end)
{
    return DB::table('books')
        ->join('book_copies', 'books.id', '=', 'book_copies.book_id')
        ->join('circulation', 'book_copies.id', '=', 'circulation.copy_id')
        ->whereBetween('circulation.issue_date', [$start, $end])
        ->select(
            'books.id',
            'books.title',
            'books.author',
            DB::raw('COUNT(circulation.id) as issue_count')
        )
        ->groupBy('books.id', 'books.title', 'books.author')
        ->orderByDesc('issue_count')
        ->limit(10)
        ->get();
}


    private function getTopMembers(Carbon $start, Carbon $end)
{
    return DB::table('members')
        ->join('circulation', 'members.id', '=', 'circulation.member_id')
        ->whereBetween('circulation.issue_date', [$start, $end])
        ->select(
            'members.id',
            'members.full_name',
            'members.member_type',
            DB::raw('COUNT(circulation.id) as issue_count')
        )
        ->groupBy('members.id', 'members.full_name', 'members.member_type')
        ->orderByDesc('issue_count')
        ->limit(10)
        ->get();
}



    private function analyzeOverdueReport($records)
{
    $total = $records->count();

    /* =====================
       MEMBER TYPE ANALYSIS
    ====================== */
    $students = $records->where('member.member_type', 'STUDENT');
    $faculty  = $records->where('member.member_type', 'FACULTY');

    /* =====================
       DURATION BUCKETS
    ====================== */
    $week1 = $records->whereBetween('overdue_days', [1, 7]);
    $week2 = $records->whereBetween('overdue_days', [8, 14]);
    $month1 = $records->whereBetween('overdue_days', [15, 30]);
    $monthPlus = $records->where('overdue_days', '>', 30);

    return [

        /* ===== MEMBER TYPE ===== */
        'students' => [
            'count' => $students->count(),
            'percentage' => $total > 0
                ? round(($students->count() / $total) * 100, 1)
                : 0,
            'avg_days' => $students->avg('overdue_days')
                ? round($students->avg('overdue_days'), 1)
                : 0,
            'total_fines' => $students->sum('calculated_fine'),
        ],

        'faculty' => [
            'count' => $faculty->count(),
            'percentage' => $total > 0
                ? round(($faculty->count() / $total) * 100, 1)
                : 0,
            'avg_days' => $faculty->avg('overdue_days')
                ? round($faculty->avg('overdue_days'), 1)
                : 0,
            'total_fines' => $faculty->sum('calculated_fine'),
        ],

        /* ===== DURATION ANALYSIS ===== */
        'duration' => [
            'week1' => [
                'count' => $week1->count(),
                'percentage' => $total > 0
                    ? round(($week1->count() / $total) * 100, 1)
                    : 0,
                'avg_fine' => $week1->avg('calculated_fine')
                    ? round($week1->avg('calculated_fine'), 2)
                    : 0,
            ],
            'week2' => [
                'count' => $week2->count(),
                'percentage' => $total > 0
                    ? round(($week2->count() / $total) * 100, 1)
                    : 0,
                'avg_fine' => $week2->avg('calculated_fine')
                    ? round($week2->avg('calculated_fine'), 2)
                    : 0,
            ],
            'month1' => [
                'count' => $month1->count(),
                'percentage' => $total > 0
                    ? round(($month1->count() / $total) * 100, 1)
                    : 0,
                'avg_fine' => $month1->avg('calculated_fine')
                    ? round($month1->avg('calculated_fine'), 2)
                    : 0,
            ],
            'month_plus' => [
                'count' => $monthPlus->count(),
                'percentage' => $total > 0
                    ? round(($monthPlus->count() / $total) * 100, 1)
                    : 0,
                'avg_fine' => $monthPlus->avg('calculated_fine')
                    ? round($monthPlus->avg('calculated_fine'), 2)
                    : 0,
            ],
        ],
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

    private function getTopPerformers(Carbon $start, Carbon $end)
{
    return DB::table('members')
        ->join('circulation', 'members.id', '=', 'circulation.member_id')
        ->whereBetween('circulation.issue_date', [$start, $end])
        ->whereNotNull('circulation.return_date')
        ->select(
            'members.id',
            'members.full_name',
            'members.member_type',
            DB::raw('COUNT(circulation.id) as total_loans')
        )
        ->groupBy('members.id', 'members.full_name', 'members.member_type')
        ->orderByDesc('total_loans')
        ->limit(10)
        ->get();
}

public function sendReminders(Request $request)
{
    $request->validate([
        'transaction_ids' => 'required|array',
    ]);

    $records = Circulation::with(['member', 'copy.book'])
        ->whereIn('id', $request->transaction_ids)
        ->get();

    foreach ($records as $record) {
        // Placeholder (email / SMS later)
        \Log::info('Reminder sent', [
            'circulation_id' => $record->id,
            'member' => $record->member->full_name ?? 'N/A',
        ]);
    }

    return response()->json([
        'message' => 'Reminders sent successfully',
    ]);
}

public function sendMessage(Request $request)
{
    $request->validate([
        'transaction_id' => 'required|integer',
        'message' => 'required|string',
    ]);

    // 🔹 Later you can add SMS / Email / WhatsApp here
    // For now we just acknowledge success

    return response()->json([
        'success' => true,
        'message' => 'Message sent successfully',
    ]);
}


    private function getInactiveMembers()
{
    return Member::doesntHave('circulations')->limit(20)->get();
}

}
