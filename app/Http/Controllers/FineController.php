<?php

namespace App\Http\Controllers;

use App\Models\Fine;
use App\Models\Circulation;
use App\Models\AuditLog;
use App\Models\LibrarySetting;
use Illuminate\Http\Request;

class FineController extends Controller
{
    /**
     * Display all fines
     */
    public function index(Request $request)
    {
        $query = Fine::with(['circulation.member', 'circulation.copy.book']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by member
        if ($request->filled('member_id')) {
            $query->whereHas('circulation.member', function ($q) use ($request) {
                $q->where('id', $request->member_id);
            });
        }

        $fines = $query
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        /*
        |--------------------------------------------------------------------------
        | Fine Summary (USED IN BLADE)
        |--------------------------------------------------------------------------
        */
        $total_pending   = Fine::where('status', 'PENDING')->sum('fine_amount');
        $total_collected = Fine::where('status', 'PAID')->sum('fine_amount');
        $total_waived    = Fine::where('status', 'WAIVED')->sum('fine_amount');
        $fine_rate       = LibrarySetting::getValue('FINE_PER_DAY', 5);

        // Optional grouped stats (future use)
        $stats = [
            'total_pending'   => $total_pending,
            'total_collected' => $total_collected,
            'total_waived'    => $total_waived,
            'fine_rate'       => $fine_rate,
        ];

        return view('fines.index', compact(
            'fines',
            'stats',
            'total_pending',
            'total_collected',
            'total_waived',
            'fine_rate'
        ));
    }

    /**
     * Mark fine as paid
     */
    public function pay($id)
    {
        $fine = Fine::findOrFail($id);

        $fine->update([
            'status'  => 'PAID',
            'paid_at' => now(),
        ]);

        AuditLog::create([
            'action_type'  => 'FINE_PAID',
            'description'  => "Fine paid: ₹{$fine->fine_amount}",
            'performed_by' => null, // no auth system
        ]);

        return redirect()->back()->with('success', 'Fine marked as paid');
    }

    /**
     * Waive fine
     */
    public function waive($id)
    {
        $fine = Fine::findOrFail($id);

        $waiverLimit = LibrarySetting::getValue('FINE_WAIVER_LIMIT', 50);

        if ($fine->fine_amount > $waiverLimit) {
            return redirect()->back()
                ->with('error', "Fine amount (₹{$fine->fine_amount}) exceeds waiver limit (₹{$waiverLimit})");
        }

        $fine->update([
            'status' => 'WAIVED',
        ]);

        AuditLog::create([
            'action_type'  => 'FINE_WAIVED',
            'description'  => "Fine waived: ₹{$fine->fine_amount}",
            'performed_by' => null,
        ]);

        return redirect()->back()->with('success', 'Fine waived successfully');
    }

    /**
     * Apply fine to a circulation
     */
    public function apply(Request $request)
    {
        $validated = $request->validate([
            'circulation_id' => 'required|exists:circulation,id',
        ]);

        $circulation = Circulation::with('member')->findOrFail($validated['circulation_id']);

        if ($circulation->fine()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Fine already applied',
            ]);
        }

        $overdueDays = $circulation->overdue_days;
        $finePerDay  = LibrarySetting::getValue('FINE_PER_DAY', 5);
        $fineAmount  = $overdueDays * $finePerDay;

        Fine::create([
            'circulation_id' => $circulation->id,
            'fine_amount'    => $fineAmount,
            'status'         => 'PENDING',
        ]);

        AuditLog::create([
            'action_type'  => 'FINE_APPLIED',
            'description'  => "Fine applied: ₹{$fineAmount} for {$circulation->member->full_name}",
            'performed_by' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fine applied successfully',
        ]);
    }

    /**
     * Apply bulk fines
     */
    public function applyBulk(Request $request)
    {
        $validated = $request->validate([
            'circulation_ids'   => 'required|array',
            'circulation_ids.*' => 'exists:circulation,id',
            'fine_per_day'      => 'required|numeric|min:0',
            'max_fine'          => 'nullable|numeric|min:0',
        ]);

        $applied = 0;
        $failed  = 0;

        foreach ($validated['circulation_ids'] as $circulationId) {
            try {
                $circulation = Circulation::findOrFail($circulationId);

                if ($circulation->fine()->exists()) {
                    $failed++;
                    continue;
                }

                $fineAmount = $circulation->overdue_days * $validated['fine_per_day'];

                if (!empty($validated['max_fine'])) {
                    $fineAmount = min($fineAmount, $validated['max_fine']);
                }

                Fine::create([
                    'circulation_id' => $circulationId,
                    'fine_amount'    => $fineAmount,
                    'status'         => 'PENDING',
                ]);

                $applied++;

            } catch (\Exception $e) {
                $failed++;
            }
        }

        AuditLog::create([
            'action_type'  => 'FINE_BULK_APPLY',
            'description'  => "Bulk fines applied: {$applied} applied, {$failed} failed",
            'performed_by' => null,
        ]);

        return redirect()->back()
            ->with('success', "Fines applied: {$applied}. Failed: {$failed}.");
    }

    /**
     * Show payment page for fine
     */
    public function paymentPage(Fine $fine)
    {
        $fine->load(['circulation.member', 'circulation.copy.book']);
        
        // Generate a dummy QR code data (in real scenario, this would be payment gateway URL)
        $qrData = "upi://pay?pa=library@bankname&pn=Library%20Management&am={$fine->fine_amount}&tr={$fine->id}";
        
        return view('fines.payment', compact('fine', 'qrData'));
    }
}
