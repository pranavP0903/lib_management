<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Book;
use App\Models\Member;
use App\Models\Circulation;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share quick stats with all views (safe if migrations haven't run)
        try {
            $totalBooks = Book::count();
            $totalMembers = Member::count();
            $activeBorrowings = Circulation::where('status', 'ISSUED')->count();
            $overdueBooks = Circulation::where('status', 'ISSUED')
                ->whereDate('due_date', '<', Carbon::today())
                ->count();

            View::share('stats', [
                'total_books' => $totalBooks,
                'total_members' => $totalMembers,
                'active_borrowings' => $activeBorrowings,
                'overdue_books' => $overdueBooks,
            ]);
        } catch (\Exception $e) {
            View::share('stats', [
                'total_books' => 0,
                'total_members' => 0,
                'active_borrowings' => 0,
                'overdue_books' => 0,
            ]);
        }
    }
}
