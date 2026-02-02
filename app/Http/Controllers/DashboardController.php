<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Member;
use App\Models\Circulation;
use App\Models\Fine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_books' => Book::count(),

            'available_copies' => DB::table('book_copies')
                ->where('status', 'AVAILABLE')
                ->count(),

            'active_borrowings' => Circulation::where('status', 'ISSUED')->count(),

            'overdue_books' => Circulation::where('status', 'ISSUED')
                ->where('due_date', '<', now())
                ->count(),

            // ✅ FIXED: used in dashboard blade
            'todays_returns' => Circulation::where('status', 'ISSUED')
                ->whereDate('due_date', today())
                ->count(),

            'total_members' => Member::count(),

            'active_members' => Member::where('status', 'ACTIVE')->count(),

            'pending_fines' => Fine::where('status', 'PENDING')
                ->sum('fine_amount'),

            'student_count' => Member::where('member_type', 'STUDENT')->count(),

            'faculty_count' => Member::where('member_type', 'FACULTY')->count(),
        ];

        $recent_transactions = Circulation::with(['member', 'copy.book'])
            ->latest()
            ->limit(10)
            ->get();

        $overdue_books = Circulation::where('status', 'ISSUED')
            ->where('due_date', '<', now())
            ->with(['member', 'copy.book'])
            ->get();

        $popular_books = Book::withCount('circulations')
            ->orderBy('circulations_count', 'desc')
            ->limit(5)
            ->get();

        return view(
            'dashboard',
            compact('stats', 'recent_transactions', 'overdue_books', 'popular_books')
        );
    }
}
