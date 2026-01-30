<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Book;
use App\Models\Member;
use App\Models\AuditLog;
use App\Models\LibrarySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    // Display all reservations
    public function index(Request $request)
    {
        $query = Reservation::with(['member', 'book']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by book
        if ($request->has('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        // Filter by member
        if ($request->has('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        $reservations = $query->orderBy('created_at', 'desc')->paginate(30);

        return view('reservations.index', compact('reservations'));
    }

    // Create new reservation
    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'book_id' => 'required|exists:books,id',
            'notes' => 'nullable|string',
        ]);

        // Check member status
        $member = Member::findOrFail($validated['member_id']);
        if ($member->status !== 'ACTIVE') {
            return redirect()->back()
                ->with('error', 'Member is not active');
        }

        // Check book availability
        $book = Book::findOrFail($validated['book_id']);
        if ($book->availableCopies() > 0) {
            return redirect()->back()
                ->with('error', 'Book is available for immediate borrowing');
        }

        // Check reservation limit
        $maxReservations = LibrarySetting::getValue('MAX_RESERVATIONS', 2);
        $activeReservations = $member->reservations()->whereIn('status', ['WAITING', 'ALLOCATED'])->count();
        
        if ($activeReservations >= $maxReservations) {
            return redirect()->back()
                ->with('error', "Member has reached maximum reservations ({$maxReservations})");
        }

        // Check if already reserved
        $existingReservation = Reservation::where('member_id', $validated['member_id'])
            ->where('book_id', $validated['book_id'])
            ->whereIn('status', ['WAITING', 'ALLOCATED'])
            ->exists();
            
        if ($existingReservation) {
            return redirect()->back()
                ->with('error', 'Member already has an active reservation for this book');
        }

        $reservation = Reservation::create([
            'member_id' => $validated['member_id'],
            'book_id' => $validated['book_id'],
            'status' => 'WAITING',
        ]);

        AuditLog::create([
            'action_type' => 'RESERVATION_CREATE',
            'description' => "Reservation created for {$book->title}",
            'performed_by' => auth()->id() ?? null,
        ]);

        return redirect()->route('reservations.index')
            ->with('success', 'Reservation created successfully');
    }

    // Allocate reserved book
    public function allocate($id)
    {
        $reservation = Reservation::with(['member', 'book'])->findOrFail($id);

        // Check if book has available copies
        if ($reservation->book->availableCopies() == 0) {
            return redirect()->back()
                ->with('error', 'No available copies to allocate');
        }

        // Get first available copy
        $copy = $reservation->book->copies()->where('status', 'AVAILABLE')->first();

        DB::beginTransaction();
        try {
            // Update reservation status
            $reservation->update(['status' => 'ALLOCATED']);

            // Update copy status
            $copy->update(['status' => 'RESERVED']);

            AuditLog::create([
                'action_type' => 'RESERVATION_ALLOCATE',
                'description' => "Reservation allocated for {$reservation->book->title}",
                'performed_by' => auth()->id() ?? null,
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Reservation allocated successfully. Copy #' . $copy->copy_number . ' is now reserved.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error allocating reservation: ' . $e->getMessage());
        }
    }

    // Cancel reservation
    public function destroy($id)
    {
        $reservation = Reservation::with('book')->findOrFail($id);
        $bookTitle = $reservation->book->title;
        
        // If reservation was allocated, free the copy
        if ($reservation->status == 'ALLOCATED') {
            $copy = $reservation->book->copies()->where('status', 'RESERVED')->first();
            if ($copy) {
                $copy->update(['status' => 'AVAILABLE']);
            }
        }

        $reservation->update(['status' => 'CANCELLED']);

        AuditLog::create([
            'action_type' => 'RESERVATION_CANCEL',
            'description' => "Reservation cancelled for {$bookTitle}",
            'performed_by' => auth()->id() ?? null,
        ]);

        return redirect()->back()
            ->with('success', 'Reservation cancelled successfully');
    }
}