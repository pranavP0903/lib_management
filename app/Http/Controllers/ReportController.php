<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Member;
use App\Models\Circulation;
use App\Models\Fine;
use App\Models\BookCopy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // Reports dashboard
    public function index()
    {
        $monthly_stats = [
            'books_issued' => Circulation::whereMonth('issue_date', now()->month)->count(),
            'books_returned' => Circulation::whereMonth('return_date', now()->month)->whereNotNull('return_date')->count(),
            'overdue_cases' => Circulation::where('status', 'OVERDUE')->whereMonth('due_date', now()->month)->count(),
            'fines_collected' => Fine::whereMonth('calculated_on', now()->month)->where('fine_status', 'PAID')->sum('fine_amount'),
        ];

        $quick_stats = [
            'total_books' => Book::count(),
            'active_members' => Member::where('status', 'ACTIVE')->count(),
            'monthly_circulation' => Circulation::whereMonth('issue_date', now()->month)->count(),
            'new_books' => Book::whereMonth('created_at', now()->month)->count(),
        ];

        $categories = Book::distinct()->pluck('category');

        return view('reports.index', compact('monthly_stats', 'quick_stats', 'categories'));
    }

    // Circulation report
    public function circulation(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $query = Circulation::with(['member', 'copy.book'])
            ->whereBetween('issue_date', [$startDate, $endDate]);

        if ($request->has('member_type')) {
            $query->whereHas('member', function($q) use ($request) {
                $q->where('member_type', $request->member_type);
            });
        }

        $circulations = $query->orderBy('issue_date', 'desc')->paginate(50);

        $summary = [
            'total_issues' => $circulations->total(),
            'total_returns' => $circulations->whereNotNull('return_date')->count(),
            'unique_members' => $circulations->unique('member_id')->count(),
            'unique_books' => $circulations->unique(function($item) {
                return $item->copy->book_id;
            })->count(),
        ];

        $stats = $this->calculateCirculationStats($circulations);
        $top_books = $this->getTopBooks($startDate, $endDate);
        $top_members = $this->getTopMembers($startDate, $endDate);

        return view('reports.circulation', compact('circulations', 'summary', 'stats', 'top_books', 'top_members'));
    }

    // Overdue report
    public function overdue(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth());
        $endDate = $request->get('end_date', now());

        $query = Circulation::with(['member', 'copy.book', 'fines'])
            ->where('status', 'ISSUED')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->where('due_date', '<', now());

        if ($request->has('member_type')) {
            $query->whereHas('member', function($q) use ($request) {
                $q->where('member_type', $request->member_type);
            });
        }

        if ($request->has('overdue_period')) {
            $now = now();
            switch ($request->overdue_period) {
                case '1-7':
                    $query->where('due_date', '>=', $now->subDays(7));
                    break;
                case '8-14':
                    $query->whereBetween('due_date', [$now->subDays(14), $now->subDays(8)]);
                    break;
                case '15-30':
                    $query->whereBetween('due_date', [$now->subDays(30), $now->subDays(15)]);
                    break;
                case '30+':
                    $query->where('due_date', '<', $now->subDays(30));
                    break;
            }
        }

        $overdue_records = $query->get();

        $summary = [
            'total_overdue' => $overdue_records->count(),
            'total_fines' => $overdue_records->sum(function($item) {
                return $item->calculated_fine;
            }),
            'avg_overdue_days' => round($overdue_records->avg('overdue_days'), 1),
            'affected_members' => $overdue_records->unique('member_id')->count(),
        ];

        $analysis = $this->analyzeOverdueReport($overdue_records);
        $repeat_offenders = $this->getRepeatOffenders($overdue_records);
        $fine_rate = \App\Models\LibrarySetting::getValue('FINE_PER_DAY', 5);

        return view('reports.overdue', compact('overdue_records', 'summary', 'analysis', 'repeat_offenders', 'fine_rate'));
    }

    // Member activity report
    public function memberActivity(Request $request)
    {
        $startDate = $request->get('start_date', now()->subYear());
        $endDate = $request->get('end_date', now());

        $query = Member::withCount([
            'circulations as total_borrowings' => function($q) use ($startDate, $endDate) {
                $q->whereBetween('issue_date', [$startDate, $endDate]);
            },
            'circulations as overdue_count' => function($q) use ($startDate, $endDate) {
                $q->whereBetween('issue_date', [$startDate, $endDate])
                  ->where('due_date', '<', now())
                  ->where('return_date', null);
            }
        ])
        ->withSum([
            'fines as fines_paid' => function($q) use ($startDate, $endDate) {
                $q->where('fine_status', 'PAID')
                  ->whereBetween('calculated_on', [$startDate, $endDate]);
            }
        ]);

        if ($request->has('member_type')) {
            $query->where('member_type', $request->member_type);
        }

        if ($request->has('activity_level')) {
            switch ($request->activity_level) {
                case 'high':
                    $query->having('total_borrowings', '>=', 5);
                    break;
                case 'medium':
                    $query->having('total_borrowings', '>=', 2)->having('total_borrowings', '<=', 4);
                    break;
                case 'low':
                    $query->having('total_borrowings', '<=', 1);
                    break;
            }
        }

        $members = $query->paginate(30);

        $summary = [
            'total_members' => $members->total(),
            'active_members' => $members->where('status', 'ACTIVE')->count(),
            'total_loans' => $members->sum('total_borrowings'),
            'avg_loans_per_member' => $members->avg('total_borrowings') ? round($members->avg('total_borrowings'), 1) : 0,
        ];

        $type_stats = $this->calculateMemberTypeStats($members);
        $top_performers = $this->getTopPerformers($startDate, $endDate);
        $inactive_members = $this->getInactiveMembers($startDate, $endDate);

        return view('reports.member-activity', compact('members', 'summary', 'type_stats', 'top_performers', 'inactive_members'));
    }

    // Inventory report
    public function inventory(Request $request)
    {
        $query = Book::selectRaw('
                books.*,
                COUNT(book_copies.copy_id) as total_copies,
                SUM(CASE WHEN book_copies.status = "AVAILABLE" THEN 1 ELSE 0 END) as available_copies,
                SUM(CASE WHEN book_copies.status = "ISSUED" THEN 1 ELSE 0 END) as issued_copies,
                SUM(CASE WHEN book_copies.status = "RESERVED" THEN 1 ELSE 0 END) as reserved_copies,
                SUM(CASE WHEN book_copies.status = "LOST" THEN 1 ELSE 0 END) as lost_copies,
                MAX(circulation.issue_date) as last_borrowed
            ')
            ->leftJoin('book_copies', 'books.book_id', '=', 'book_copies.book_id')
            ->leftJoin('circulation', 'book_copies.copy_id', '=', 'circulation.copy_id')
            ->groupBy('books.book_id');

        // Apply filters
        if ($request->has('category')) {
            $query->where('books.category', $request->category);
        }

        if ($request->has('status')) {
            $query->havingRaw('SUM(CASE WHEN book_copies.status = ? THEN 1 ELSE 0 END) > 0', [$request->status]);
        }

        if ($request->has('year')) {
            $query->whereYear('books.created_at', $request->year);
        }

        // Apply sorting
        switch ($request->get('sort', 'title')) {
            case 'title_desc':
                $query->orderBy('books.title', 'desc');
                break;
            case 'popular':
                $query->orderByRaw('COUNT(circulation.transaction_id) DESC');
                break;
            case 'recent':
                $query->orderBy('books.created_at', 'desc');
                break;
            default:
                $query->orderBy('books.title', 'asc');
        }

        $inventory = $query->paginate(30);

        $summary = [
            'total_books' => Book::count(),
            'total_copies' => BookCopy::count(),
            'available_copies' => BookCopy::where('status', 'AVAILABLE')->count(),
            'issued_copies' => BookCopy::where('status', 'ISSUED')->count(),
            'reserved_copies' => BookCopy::where('status', 'RESERVED')->count(),
            'lost_copies' => BookCopy::where('status', 'LOST')->count(),
            'available_percentage' => BookCopy::count() > 0 ? round((BookCopy::where('status', 'AVAILABLE')->count() / BookCopy::count()) * 100, 1) : 0,
            'issued_percentage' => BookCopy::count() > 0 ? round((BookCopy::where('status', 'ISSUED')->count() / BookCopy::count()) * 100, 1) : 0,
            'digital_books' => Book::whereNotNull('digital_resource_url')->count(),
        ];

        $category_stats = $this->calculateCategoryStats();
        $aging = $this->calculateAgingAnalysis();
        $digital_books = Book::whereNotNull('digital_resource_url')->get();
        $categories = Book::distinct()->pluck('category');

        return view('reports.inventory', compact('inventory', 'summary', 'category_stats', 'aging', 'digital_books', 'categories'));
    }

    // Helper methods for reports
    private function calculateCirculationStats($circulations)
    {
        $students = $circulations->filter(function($item) {
            return $item->member->member_type == 'STUDENT';
        });

        $faculty = $circulations->filter(function($item) {
            return $item->member->member_type == 'FACULTY';
        });

        return [
            'student_issues' => $students->count(),
            'student_percentage' => $circulations->count() > 0 ? round(($students->count() / $circulations->count()) * 100, 1) : 0,
            'student_avg_duration' => $students->avg(function($item) {
                return $item->return_date ? $item->issue_date->diffInDays($item->return_date) : null;
            }) ?? 0,
            'faculty_issues' => $faculty->count(),
            'faculty_percentage' => $circulations->count() > 0 ? round(($faculty->count() / $circulations->count()) * 100, 1) : 0,
            'faculty_avg_duration' => $faculty->avg(function($item) {
                return $item->return_date ? $item->issue_date->diffInDays($item->return_date) : null;
            }) ?? 0,
        ];
    }

    private function getTopBooks($startDate, $endDate)
    {
        return Book::selectRaw('
                books.*,
                COUNT(circulation.transaction_id) as issue_count,
                AVG(DATEDIFF(circulation.return_date, circulation.issue_date)) as avg_duration
            ')
            ->leftJoin('book_copies', 'books.book_id', '=', 'book_copies.book_id')
            ->leftJoin('circulation', 'book_copies.copy_id', '=', 'circulation.copy_id')
            ->whereBetween('circulation.issue_date', [$startDate, $endDate])
            ->groupBy('books.book_id')
            ->orderBy('issue_count', 'desc')
            ->limit(10)
            ->get();
    }

    private function getTopMembers($startDate, $endDate)
    {
        return Member::selectRaw('
                members.*,
                COUNT(circulation.transaction_id) as issue_count,
                AVG(DATEDIFF(circulation.return_date, circulation.issue_date)) as avg_return_days
            ')
            ->leftJoin('circulation', 'members.member_id', '=', 'circulation.member_id')
            ->whereBetween('circulation.issue_date', [$startDate, $endDate])
            ->groupBy('members.member_id')
            ->orderBy('issue_count', 'desc')
            ->limit(10)
            ->get();
    }

    private function analyzeOverdueReport($overdue_records)
    {
        $students = $overdue_records->filter(function($item) {
            return $item->member->member_type == 'STUDENT';
        });

        $faculty = $overdue_records->filter(function($item) {
            return $item->member->member_type == 'FACULTY';
        });

        $duration_groups = [
            'week1' => $overdue_records->filter(fn($item) => $item->overdue_days <= 7),
            'week2' => $overdue_records->filter(fn($item) => $item->overdue_days > 7 && $item->overdue_days <= 14),
            'month1' => $overdue_records->filter(fn($item) => $item->overdue_days > 14 && $item->overdue_days <= 30),
            'month_plus' => $overdue_records->filter(fn($item) => $item->overdue_days > 30),
        ];

        return [
            'students' => [
                'count' => $students->count(),
                'percentage' => $overdue_records->count() > 0 ? round(($students->count() / $overdue_records->count()) * 100, 1) : 0,
                'avg_days' => $students->avg('overdue_days') ? round($students->avg('overdue_days'), 1) : 0,
                'total_fines' => $students->sum('calculated_fine'),
            ],
            'faculty' => [
                'count' => $faculty->count(),
                'percentage' => $overdue_records->count() > 0 ? round(($faculty->count() / $overdue_records->count()) * 100, 1) : 0,
                'avg_days' => $faculty->avg('overdue_days') ? round($faculty->avg('overdue_days'), 1) : 0,
                'total_fines' => $faculty->sum('calculated_fine'),
            ],
            'duration' => collect($duration_groups)->map(function($group, $key) use ($overdue_records) {
                return [
                    'count' => $group->count(),
                    'percentage' => $overdue_records->count() > 0 ? round(($group->count() / $overdue_records->count()) * 100, 1) : 0,
                    'avg_fine' => $group->avg('calculated_fine') ? round($group->avg('calculated_fine'), 2) : 0,
                ];
            })->toArray(),
        ];
    }

    private function getRepeatOffenders($overdue_records)
    {
        $offenders = $overdue_records->groupBy('member_id')->map(function($group) {
            return [
                'member' => $group->first()->member,
                'overdue_count' => $group->count(),
                'avg_overdue_days' => round($group->avg('overdue_days'), 1),
                'total_fines' => $group->sum('calculated_fine'),
            ];
        })->sortByDesc('overdue_count')->take(10);

        return $offenders->values();
    }

    private function calculateMemberTypeStats($members)
    {
        $students = $members->filter(fn($m) => $m->member_type == 'STUDENT');
        $faculty = $members->filter(fn($m) => $m->member_type == 'FACULTY');

        return [
            'students' => [
                'total' => $students->count(),
                'active' => $students->where('status', 'ACTIVE')->count(),
                'loans' => $students->sum('total_borrowings'),
                'avg_loans' => $students->avg('total_borrowings') ? round($students->avg('total_borrowings'), 1) : 0,
                'overdue_percentage' => $students->sum('total_borrowings') > 0 ? 
                    round(($students->sum('overdue_count') / $students->sum('total_borrowings')) * 100, 1) : 0,
            ],
            'faculty' => [
                'total' => $faculty->count(),
                'active' => $faculty->where('status', 'ACTIVE')->count(),
                'loans' => $faculty->sum('total_borrowings'),
                'avg_loans' => $faculty->avg('total_borrowings') ? round($faculty->avg('total_borrowings'), 1) : 0,
                'overdue_percentage' => $faculty->sum('total_borrowings') > 0 ? 
                    round(($faculty->sum('overdue_count') / $faculty->sum('total_borrowings')) * 100, 1) : 0,
            ],
        ];
    }

    private function getTopPerformers($startDate, $endDate)
    {
        return Member::selectRaw('
                members.*,
                COUNT(circulation.transaction_id) as total_loans,
                SUM(CASE WHEN circulation.return_date <= circulation.due_date THEN 1 ELSE 0 END) as ontime_returns,
                (SUM(CASE WHEN circulation.return_date <= circulation.due_date THEN 1 ELSE 0 END) * 100.0 / COUNT(circulation.transaction_id)) as ontime_percentage
            ')
            ->leftJoin('circulation', 'members.member_id', '=', 'circulation.member_id')
            ->whereBetween('circulation.issue_date', [$startDate, $endDate])
            ->whereNotNull('circulation.return_date')
            ->groupBy('members.member_id')
            ->having('total_loans', '>=', 3)
            ->orderBy('ontime_percentage', 'desc')
            ->limit(10)
            ->get()
            ->map(function($member) {
                $member->ontime_returns = $member->ontime_percentage ? round($member->ontime_percentage, 0) : 0;
                $member->activity_score = $member->total_loans > 0 ? min(100, $member->total_loans * 10 + $member->ontime_returns) : 0;
                return $member;
            });
    }

    private function getInactiveMembers($startDate, $endDate)
    {
        return Member::selectRaw('
                members.*,
                MAX(circulation.issue_date) as last_activity,
                COUNT(circulation.transaction_id) as total_loans,
                DATEDIFF(NOW(), MAX(circulation.issue_date)) as days_inactive
            ')
            ->leftJoin('circulation', 'members.member_id', '=', 'circulation.member_id')
            ->where('members.status', 'ACTIVE')
            ->groupBy('members.member_id')
            ->havingRaw('MAX(circulation.issue_date) IS NULL OR DATEDIFF(NOW(), MAX(circulation.issue_date)) > 30')
            ->orderBy('days_inactive', 'desc')
            ->limit(20)
            ->get();
    }

    private function calculateCategoryStats()
    {
        return Book::selectRaw('
                category as name,
                COUNT(*) as book_count,
                COUNT(book_copies.copy_id) as copy_count,
                SUM(CASE WHEN book_copies.status = "AVAILABLE" THEN 1 ELSE 0 END) as available_count
            ')
            ->leftJoin('book_copies', 'books.book_id', '=', 'book_copies.book_id')
            ->groupBy('books.category')
            ->orderBy('book_count', 'desc')
            ->get();
    }

    private function calculateAgingAnalysis()
    {
        $now = now();

        return [
            'new' => [
                'count' => Book::where('created_at', '>=', $now->subYear())->count(),
                'percentage' => Book::count() > 0 ? round((Book::where('created_at', '>=', $now->subYear())->count() / Book::count()) * 100, 1) : 0,
                'avg_borrowings' => $this->calculateAvgBorrowingsForAge($now->subYear(), $now),
            ],
            'recent' => [
                'count' => Book::whereBetween('created_at', [$now->subYears(3), $now->subYear()])->count(),
                'percentage' => Book::count() > 0 ? round((Book::whereBetween('created_at', [$now->subYears(3), $now->subYear()])->count() / Book::count()) * 100, 1) : 0,
                'avg_borrowings' => $this->calculateAvgBorrowingsForAge($now->subYears(3), $now->subYear()),
            ],
            'middle' => [
                'count' => Book::whereBetween('created_at', [$now->subYears(5), $now->subYears(3)])->count(),
                'percentage' => Book::count() > 0 ? round((Book::whereBetween('created_at', [$now->subYears(5), $now->subYears(3)])->count() / Book::count()) * 100, 1) : 0,
                'avg_borrowings' => $this->calculateAvgBorrowingsForAge($now->subYears(5), $now->subYears(3)),
            ],
            'old' => [
                'count' => Book::where('created_at', '<', $now->subYears(5))->count(),
                'percentage' => Book::count() > 0 ? round((Book::where('created_at', '<', $now->subYears(5))->count() / Book::count()) * 100, 1) : 0,
                'avg_borrowings' => $this->calculateAvgBorrowingsForAge(null, $now->subYears(5)),
            ],
        ];
    }

    private function calculateAvgBorrowingsForAge($startDate, $endDate)
    {
        $query = Book::leftJoin('book_copies', 'books.book_id', '=', 'book_copies.book_id')
            ->leftJoin('circulation', 'book_copies.copy_id', '=', 'circulation.copy_id');

        if ($startDate) {
            $query->where('books.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('books.created_at', '<', $endDate);
        }

        $result = $query->selectRaw('COUNT(DISTINCT circulation.transaction_id) / COUNT(DISTINCT books.book_id) as avg_borrowings')
            ->first();

        return $result ? round($result->avg_borrowings, 1) : 0;
    }
}