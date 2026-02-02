<?php

namespace App\Http\Controllers;

use App\Models\Circulation;
use App\Models\Member;
use App\Models\BookCopy;
use App\Models\Fine;
use App\Models\Reservation;
use App\Models\AuditLog;
use App\Models\LibrarySetting;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CirculationController extends Controller
{
    /* =====================================================
     | ISSUE BOOK
     ===================================================== */
    public function create(Request $request)
    {
        $member = $request->member_id ? Member::find($request->member_id) : null;
        $book   = $request->book_id ? Book::find($request->book_id) : null;
        $copy   = $request->copy_id ? BookCopy::find($request->copy_id) : null;

        return view('circulation.issue', compact('member', 'book', 'copy'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id'  => 'required|exists:members,id',
            'copy_id'    => 'required|exists:book_copies,id',
            'issue_date' => 'required|date',
            'due_date'   => 'required|date|after:issue_date',
            'notes'      => 'nullable|string',
        ]);

        $member = Member::findOrFail($validated['member_id']);

        if ($member->status !== 'ACTIVE') {
            return back()->with('error', 'Member is not active');
        }

        if ($member->activeBorrowings()->count() >= $member->borrow_limit) {
            return back()->with('error', 'Borrow limit reached');
        }

        if ($member->fines()->where('fines.status', 'PENDING')->sum('fine_amount') > 0) {
            return back()->with('error', 'Member has pending fines');
        }

        $copy = BookCopy::findOrFail($validated['copy_id']);

        if ($copy->status !== 'AVAILABLE') {
            return back()->with('error', 'Book copy not available');
        }

        DB::transaction(function () use ($validated, $member, $copy) {
            Circulation::create([
                'member_id'  => $member->id,
                'copy_id'    => $copy->id,
                'issue_date' => $validated['issue_date'],
                'due_date'   => $validated['due_date'],
                'status'     => 'ISSUED',
                'notes'      => $validated['notes'] ?? null,
            ]);

            $copy->update(['status' => 'ISSUED']);

            Reservation::where('book_id', $copy->book_id)
                ->where('member_id', $member->id)
                ->where('status', 'WAITING')
                ->update(['status' => 'ALLOCATED']);

            AuditLog::create([
                'action_type' => 'BOOK_ISSUE',
                'description' => "Book issued to {$member->full_name}",
                'performed_by' => null,
            ]);
        });

        return redirect()->route('circulation.active')
            ->with('success', 'Book issued successfully');
    }

    /* =====================================================
     | RETURN BOOK
     ===================================================== */
    public function returnForm()
    {
        $query = Circulation::with(['member', 'copy.book'])
            ->where('status', 'ISSUED')
            ->orderBy('due_date');

        $activeBorrowings = $query->get();

        // Allow search by query string (copy id, copy number, ISBN, title, member name/email/phone)
        $active_borrowings = null;
        if (request()->filled('q')) {
            $q = request('q');

            $active_borrowings = Circulation::with(['member', 'copy.book'])
                ->where('status', 'ISSUED')
                ->where(function($r) use ($q) {
                    $r->whereHas('copy', function($c) use ($q) {
                        $c->where('id', $q)
                          ->orWhere('copy_number', 'like', "%{$q}%");
                    })
                    ->orWhereHas('copy.book', function($b) use ($q) {
                        $b->where('isbn', $q)
                          ->orWhere('title', 'like', "%{$q}%");
                    })
                    ->orWhereHas('member', function($m) use ($q) {
                        $m->where('full_name', 'like', "%{$q}%")
                          ->orWhere('email', 'like', "%{$q}%")
                          ->orWhere('phone', 'like', "%{$q}%");
                    });
                })
                ->orderBy('due_date')
                ->get();
        }

        // 👇 FIXED: pass real variables (not array)
        $today_returns   = Circulation::where('status', 'ISSUED')
            ->whereDate('due_date', today())
            ->count();

        $week_returns    = Circulation::where('status', 'ISSUED')
            ->whereBetween('due_date', [today(), today()->addDays(7)])
            ->count();

        $overdue_returns = Circulation::where('status', 'ISSUED')
            ->where('due_date', '<', today())
            ->count();

        return view('circulation.return', compact(
            'activeBorrowings',
            'today_returns',
            'week_returns',
            'overdue_returns',
            'active_borrowings'
        ));
    }

    public function returnBook(Request $request)
    {
        $validated = $request->validate([
            'circulation_id' => 'required|exists:circulation,id',
            'return_date'    => 'required|date|after_or_equal:issue_date',
        ]);

        $circulation = Circulation::with(['member', 'copy'])->findOrFail($validated['circulation_id']);

        DB::transaction(function () use ($circulation, $validated) {
            $circulation->update([
                'return_date' => $validated['return_date'],
                'status'      => 'RETURNED',
            ]);

            $circulation->copy->update(['status' => 'AVAILABLE']);

            // Apply fine safely
            if ($circulation->due_date->lt($validated['return_date']) &&
                !$circulation->fine()->exists()) {

                $days = $circulation->due_date->diffInDays($validated['return_date']);
                $rate = LibrarySetting::getValue('FINE_PER_DAY', 5);

                Fine::create([
                    'circulation_id' => $circulation->id,
                    'fine_amount'    => $days * $rate,
                    'status'         => 'PENDING',
                ]);
            }

            AuditLog::create([
                'action_type' => 'BOOK_RETURN',
                'description' => "Book returned by {$circulation->member->full_name}",
                'performed_by' => null,
            ]);
        });

        return redirect()->route('circulation.return')
            ->with('success', 'Book returned successfully');
    }

    /* =====================================================
     | ACTIVE & OVERDUE
     ===================================================== */
    public function active()
    {
        $borrowings = Circulation::with(['member', 'copy.book'])
            ->where('status', 'ISSUED')
            ->orderBy('due_date')
            ->paginate(30);

        // Calculate stats
        $allBorrowings = Circulation::where('status', 'ISSUED')->get();
        $now = Carbon::now();
        
        $stats = [
            'total' => $allBorrowings->count(),
            'overdue' => $allBorrowings->filter(fn($c) => $c->due_date < $now)->count(),
            'due_today' => $allBorrowings->filter(fn($c) => $c->due_date->isToday())->count(),
        ];

        return view('circulation.active', compact('borrowings', 'stats'));
    }

    /* =====================================================
     | OVERDUE
     ===================================================== */

    public function overdue()
{
    $fine_rate = LibrarySetting::getValue('FINE_PER_DAY', 5);

    // Fetch overdue borrowings
    $overdues = Circulation::with(['member', 'copy.book', 'fine'])
        ->where('status', 'ISSUED')
        ->where('due_date', '<', now())
        ->get()
        ->map(function ($c) use ($fine_rate) {

            $overdue_days = $c->due_date->diffInDays(now());

            return $c->setAttribute('transaction_id', $c->id)
                     ->setAttribute('overdue_days', $overdue_days)
                     ->setAttribute('calculated_fine', $overdue_days * $fine_rate)
                     ->setAttribute('fine_applied', $c->fine()->exists());
        });

    // -----------------------------
    // SUMMARY METRICS
    // -----------------------------
    $total_fines = $overdues->sum('calculated_fine');

    $max_overdue_days = $overdues->max('overdue_days') ?? 0;

    $affected_members = $overdues
        ->pluck('member_id')
        ->unique()
        ->count();

    // -----------------------------
    // MEMBER TYPE STATS
    // -----------------------------
    $students = $overdues->filter(
        fn ($c) => $c->member->member_type === 'STUDENT'
    )->count();

    $faculty = $overdues->filter(
        fn ($c) => $c->member->member_type === 'FACULTY'
    )->count();

    $member_total = max($students + $faculty, 1);

    // -----------------------------
    // OVERDUE DURATION STATS
    // -----------------------------
    $week1 = $overdues->filter(fn ($c) => $c->overdue_days <= 7)->count();
    $week2 = $overdues->filter(fn ($c) => $c->overdue_days >= 8 && $c->overdue_days <= 14)->count();
    $month1 = $overdues->filter(fn ($c) => $c->overdue_days >= 15 && $c->overdue_days <= 30)->count();
    $month_plus = $overdues->filter(fn ($c) => $c->overdue_days > 30)->count();

    $duration_total = max($week1 + $week2 + $month1 + $month_plus, 1);

    // -----------------------------
    // FINAL STATS ARRAY (USED BY BLADE)
    // -----------------------------
    $stats = [
        'students' => $students,
        'faculty' => $faculty,
        'students_percentage' => round(($students / $member_total) * 100, 1),
        'faculty_percentage' => round(($faculty / $member_total) * 100, 1),

        'week1' => $week1,
        'week2' => $week2,
        'month1' => $month1,
        'month_plus' => $month_plus,

        'week1_percentage' => round(($week1 / $duration_total) * 100, 1),
        'week2_percentage' => round(($week2 / $duration_total) * 100, 1),
        'month1_percentage' => round(($month1 / $duration_total) * 100, 1),
        'month_plus_percentage' => round(($month_plus / $duration_total) * 100, 1),
    ];

    return view('circulation.overdue', compact(
        'overdues',
        'total_fines',
        'max_overdue_days',
        'affected_members',
        'fine_rate',
        'stats'
    ));
}

/* =====================================================
 | SEND OVERDUE ALERTS
 ===================================================== */

public function sendOverdueAlerts()
{
    $overdues = Circulation::with('member')
        ->where('status', 'ISSUED')
        ->where('due_date', '<', now())
        ->get();

    $members = $overdues
        ->pluck('member')
        ->unique('id');

    // 🔔 For now we just simulate sending alerts
    // Later you can add Mail / WhatsApp / SMS here
    foreach ($members as $member) {
        // Example:
        // Mail::to($member->email)->send(new OverdueAlertMail($member));
    }

    return response()->json([
        'message' => 'Overdue alerts sent to ' . $members->count() . ' members successfully.'
    ]);
}

/* =====================================================
 | SEND MESSAGE TO MEMBER
 ===================================================== */

public function sendMessage(Request $request)
{
    $request->validate([
        'transaction_id' => 'required',
        'message' => 'required|string'
    ]);

    $circulation = Circulation::with('member')->findOrFail($request->transaction_id);

    // 🔔 For now we simulate message sending
    // Later you can plug Email / WhatsApp / SMS APIs here

    AuditLog::create([
        'action_type' => 'OVERDUE_MESSAGE_SENT',
        'description' => "Message sent to {$circulation->member->full_name}",
        'performed_by' => null,
    ]);

    return response()->json([
        'message' => 'Message sent successfully to ' . $circulation->member->full_name
    ]);
}

    /* =====================================================
     | RENEW
     ===================================================== */
    public function renew($id)
    {
        $circulation = Circulation::with('member')->findOrFail($id);

        if ($circulation->status !== 'ISSUED') {
            return back()->with('error', 'Cannot renew returned book');
        }

        if ($circulation->renewals >= 2) {
            return back()->with('error', 'Maximum renewals reached');
        }

        $days = $circulation->member->member_type === 'FACULTY' ? 14 : 7;

        $circulation->update([
            'due_date' => $circulation->due_date->addDays($days),
            'renewals' => $circulation->renewals + 1,
        ]);

        return back()->with('success', 'Book renewed successfully');
    }
}
