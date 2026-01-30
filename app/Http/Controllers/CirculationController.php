<?php

namespace App\Http\Controllers;

use App\Models\Circulation;
use App\Models\Member;
use App\Models\BookCopy;
use App\Models\Fine;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CirculationController extends Controller
{
    // Show issue book form
    public function create(Request $request)
    {
        $memberId = $request->get('member_id');
        $bookId = $request->get('book_id');
        $copyId = $request->get('copy_id');

        $member = $memberId ? Member::find($memberId) : null;
        $book = $bookId ? \App\Models\Book::find($bookId) : null;
        $copy = $copyId ? BookCopy::find($copyId) : null;

        return view('circulation.issue', compact('member', 'book', 'copy'));
    }

    // Issue book to member
    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,member_id',
            'copy_id' => 'required|exists:book_copies,copy_id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after:issue_date',
            'notes' => 'nullable|string',
        ]);

        // Check member status
        $member = Member::findOrFail($validated['member_id']);
        if ($member->status !== 'ACTIVE') {
            return redirect()->back()
                ->with('error', 'Member is not active');
        }

        // Check borrowing limit
        $activeBorrowings = $member->activeBorrowings()->count();
        if ($activeBorrowings >= $member->borrow_limit) {
            return redirect()->back()
                ->with('error', "Member has reached borrowing limit ({$member->borrow_limit})");
        }

        // Check pending fines
        $pendingFines = $member->fines()->where('fine_status', 'PENDING')->sum('fine_amount');
        if ($pendingFines > 0) {
            return redirect()->back()
                ->with('error', "Member has pending fines: ₹{$pendingFines}");
        }

        // Check copy availability
        $copy = BookCopy::findOrFail($validated['copy_id']);
        if ($copy->status !== 'AVAILABLE') {
            return redirect()->back()
                ->with('error', 'Book copy is not available');
        }

        DB::beginTransaction();
        try {
            // Create circulation record
            $circulation = Circulation::create([
                'member_id' => $validated['member_id'],
                'copy_id' => $validated['copy_id'],
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'],
                'status' => 'ISSUED',
            ]);

            // Update copy status
            $copy->update(['status' => 'ISSUED']);

            // Check and update any reservations
            $reservation = Reservation::where('book_id', $copy->book_id)
                ->where('member_id', $validated['member_id'])
                ->where('status', 'WAITING')
                ->first();
                
            if ($reservation) {
                $reservation->update(['status' => 'ALLOCATED']);
            }

            AuditLog::log('BOOK_ISSUE', "Book issued to {$member->full_name}");

            DB::commit();

            return redirect()->route('circulation.active')
                ->with('success', 'Book issued successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error issuing book: ' . $e->getMessage());
        }
    }

    // Show return book form
    public function returnForm()
    {
        $activeBorrowings = Circulation::with(['member', 'copy.book'])
            ->where('status', 'ISSUED')
            ->orderBy('due_date', 'asc')
            ->limit(50)
            ->get();

        $stats = [
            'today_returns' => Circulation::whereDate('due_date', today())->where('status', 'ISSUED')->count(),
            'week_returns' => Circulation::whereBetween('due_date', [today(), today()->addDays(7)])->where('status', 'ISSUED')->count(),
            'overdue_returns' => Circulation::where('due_date', '<', today())->where('status', 'ISSUED')->count(),
        ];

        return view('circulation.return', compact('activeBorrowings', 'stats'));
    }

    // Process book return
    public function returnBook(Request $request)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|exists:circulation,transaction_id',
            'return_date' => 'required|date',
            'condition' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $circulation = Circulation::with(['member', 'copy'])->findOrFail($validated['transaction_id']);

        DB::beginTransaction();
        try {
            // Update circulation record
            $circulation->update([
                'return_date' => $validated['return_date'],
                'status' => 'RETURNED',
            ]);

            // Update copy status
            $circulation->copy->update(['status' => 'AVAILABLE']);

            // Check for overdue and apply fine
            if ($circulation->due_date < $validated['return_date']) {
                $overdueDays = $circulation->due_date->diffInDays($validated['return_date']);
                $finePerDay = \App\Models\LibrarySetting::getValue('FINE_PER_DAY', 5);
                $fineAmount = $overdueDays * $finePerDay;

                Fine::create([
                    'transaction_id' => $circulation->transaction_id,
                    'fine_amount' => $fineAmount,
                    'fine_status' => 'PENDING',
                ]);
            }

            // Check for waiting reservations
            $reservation = Reservation::where('book_id', $circulation->copy->book_id)
                ->where('status', 'WAITING')
                ->orderBy('reservation_date', 'asc')
                ->first();
                
            if ($reservation) {
                $reservation->update(['status' => 'ALLOCATED']);
                // Here you could send notification to the member
            }

            AuditLog::log('BOOK_RETURN', "Book returned by {$circulation->member->full_name}");

            DB::commit();

            return redirect()->route('circulation.return.form')
                ->with('success', 'Book returned successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error returning book: ' . $e->getMessage());
        }
    }

    // Show active borrowings
    public function active(Request $request)
    {
        $query = Circulation::with(['member', 'copy.book'])
            ->where('status', 'ISSUED');

        // Filter by member type
        if ($request->has('member_type')) {
            $query->whereHas('member', function($q) use ($request) {
                $q->where('member_type', $request->member_type);
            });
        }

        // Filter by overdue status
        if ($request->has('overdue')) {
            if ($request->overdue == 'overdue') {
                $query->where('due_date', '<', now());
            } elseif ($request->overdue == 'due_soon') {
                $query->whereBetween('due_date', [now(), now()->addDays(3)]);
            }
        }

        // Filter by due date
        if ($request->has('due_date')) {
            $query->whereDate('due_date', $request->due_date);
        }

        $loans = $query->orderBy('due_date', 'asc')->paginate(30);

        $stats = [
            'total' => $loans->total(),
            'overdue' => Circulation::where('status', 'ISSUED')->where('due_date', '<', now())->count(),
            'due_today' => Circulation::where('status', 'ISSUED')->whereDate('due_date', today())->count(),
        ];

        return view('circulation.active', compact('loans', 'stats'));
    }

    // Show overdue borrowings
    public function overdue(Request $request)
    {
        $query = Circulation::with(['member', 'copy.book', 'fines'])
            ->where('status', 'ISSUED')
            ->where('due_date', '<', now());

        // Filter by member type
        if ($request->has('member_type')) {
            $query->whereHas('member', function($q) use ($request) {
                $q->where('member_type', $request->member_type);
            });
        }

        // Filter by overdue period
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

        $overdue = $query->orderBy('due_date', 'asc')->get();

        $stats = [
            'total_fines' => $overdue->sum(function($item) {
                return $item->calculated_fine;
            }),
            'max_overdue_days' => $overdue->max(function($item) {
                return $item->overdue_days;
            }),
            'affected_members' => $overdue->unique('member_id')->count(),
        ];

        $analysis = $this->analyzeOverdue($overdue);

        return view('circulation.overdue', compact('overdue', 'stats', 'analysis'));
    }

    // Renew borrowing
    public function renew($id)
    {
        $circulation = Circulation::with(['member'])->findOrFail($id);

        // Check if already renewed
        if ($circulation->renewals >= 2) {
            return redirect()->back()
                ->with('error', 'Maximum renewals reached (2)');
        }

        // Calculate new due date based on member type
        $member = $circulation->member;
        $borrowDays = $member->member_type == 'FACULTY' ? 14 : 7;
        $newDueDate = $circulation->due_date->addDays($borrowDays);

        $circulation->update([
            'due_date' => $newDueDate,
            'renewals' => $circulation->renewals + 1,
        ]);

        AuditLog::log('BOOK_RENEW', "Book renewed for {$member->full_name}");

        return redirect()->back()
            ->with('success', 'Book renewed successfully. New due date: ' . $newDueDate->format('M d, Y'));
    }

    // Analyze overdue data
    private function analyzeOverdue($overdue)
    {
        $students = $overdue->filter(function($item) {
            return $item->member->member_type == 'STUDENT';
        });

        $faculty = $overdue->filter(function($item) {
            return $item->member->member_type == 'FACULTY';
        });

        return [
            'students' => [
                'count' => $students->count(),
                'percentage' => $overdue->count() > 0 ? round(($students->count() / $overdue->count()) * 100, 1) : 0,
                'avg_days' => $students->avg('overdue_days') ? round($students->avg('overdue_days'), 1) : 0,
                'total_fines' => $students->sum('calculated_fine'),
            ],
            'faculty' => [
                'count' => $faculty->count(),
                'percentage' => $overdue->count() > 0 ? round(($faculty->count() / $overdue->count()) * 100, 1) : 0,
                'avg_days' => $faculty->avg('overdue_days') ? round($faculty->avg('overdue_days'), 1) : 0,
                'total_fines' => $faculty->sum('calculated_fine'),
            ],
        ];
    }
}