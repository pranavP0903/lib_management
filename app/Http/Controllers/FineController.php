<?php

namespace App\Http\Controllers;

use App\Models\Fine;
use App\Models\Circulation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FineController extends Controller
{
    // Display all fines
    public function index(Request $request)
    {
        $query = Fine::with(['circulation.member', 'circulation.copy.book']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('fine_status', $request->status);
        }

        // Filter by member
        if ($request->has('member_id')) {
            $query->whereHas('circulation.member', function($q) use ($request) {
                $q->where('member_id', $request->member_id);
            });
        }

        $fines = $query->orderBy('calculated_on', 'desc')->paginate(30);

        $stats = [
            'total_pending' => Fine::where('fine_status', 'PENDING')->sum('fine_amount'),
            'total_collected' => Fine::where('fine_status', 'PAID')->sum('fine_amount'),
            'total_waived' => Fine::where('fine_status', 'WAIVED')->sum('fine_amount'),
            'fine_rate' => \App\Models\LibrarySetting::getValue('FINE_PER_DAY', 5),
        ];

        return view('fines.index', compact('fines', 'stats'));
    }

    // Mark fine as paid
    public function pay($id)
    {
        $fine = Fine::findOrFail($id);
        $fine->update(['fine_status' => 'PAID']);

        AuditLog::log('FINE_PAID', "Fine paid: ₹{$fine->fine_amount}");

        return redirect()->back()
            ->with('success', 'Fine marked as paid');
    }

    // Waive fine
    public function waive($id)
    {
        $fine = Fine::findOrFail($id);
        
        // Check waiver limit
        $waiverLimit = \App\Models\LibrarySetting::getValue('FINE_WAIVER_LIMIT', 50);
        if ($fine->fine_amount > $waiverLimit) {
            return redirect()->back()
                ->with('error', "Fine amount (₹{$fine->fine_amount}) exceeds waiver limit (₹{$waiverLimit})");
        }

        $fine->update(['fine_status' => 'WAIVED']);

        AuditLog::log('FINE_WAIVED', "Fine waived: ₹{$fine->fine_amount}");

        return redirect()->back()
            ->with('success', 'Fine waived successfully');
    }

    // Apply fine to overdue borrowing
    public function apply(Request $request)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|exists:circulation,transaction_id',
        ]);

        $circulation = Circulation::with('member')->findOrFail($validated['transaction_id']);

        // Check if already fined
        if ($circulation->fine()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Fine already applied'
            ]);
        }

        // Calculate fine
        $overdueDays = $circulation->overdue_days;
        $finePerDay = \App\Models\LibrarySetting::getValue('FINE_PER_DAY', 5);
        $fineAmount = $overdueDays * $finePerDay;

        Fine::create([
            'transaction_id' => $circulation->transaction_id,
            'fine_amount' => $fineAmount,
            'fine_status' => 'PENDING',
        ]);

        AuditLog::log('FINE_APPLIED', "Fine applied: ₹{$fineAmount} for {$circulation->member->full_name}");

        return response()->json([
            'success' => true,
            'message' => 'Fine applied successfully'
        ]);
    }

    // Apply bulk fines
    public function applyBulk(Request $request)
    {
        $validated = $request->validate([
            'loan_ids' => 'required|array',
            'loan_ids.*' => 'exists:circulation,transaction_id',
            'fine_per_day' => 'required|numeric|min:0',
            'max_fine' => 'nullable|numeric|min:0',
        ]);

        $applied = 0;
        $failed = 0;

        foreach ($validated['loan_ids'] as $transactionId) {
            try {
                $circulation = Circulation::findOrFail($transactionId);

                // Skip if already fined
                if ($circulation->fine()->exists()) {
                    $failed++;
                    continue;
                }

                // Calculate fine
                $overdueDays = $circulation->overdue_days;
                $fineAmount = $overdueDays * $validated['fine_per_day'];

                // Apply maximum fine limit if set
                if (isset($validated['max_fine']) && $validated['max_fine'] > 0) {
                    $fineAmount = min($fineAmount, $validated['max_fine']);
                }

                Fine::create([
                    'transaction_id' => $transactionId,
                    'fine_amount' => $fineAmount,
                    'fine_status' => 'PENDING',
                ]);

                $applied++;

            } catch (\Exception $e) {
                $failed++;
            }
        }

        AuditLog::log('FINE_BULK_APPLY', "Bulk fines applied: {$applied} fines, {$failed} failed");

        return redirect()->back()
            ->with('success', "Fines applied to {$applied} borrowings. {$failed} failed.");
    }
}