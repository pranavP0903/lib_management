<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Circulation;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    /* =======================
       LIST MEMBERS
    ======================== */
    public function index(Request $request)
    {
        $query = Member::query()
            ->withCount([
                // ACTIVE borrowings
                'circulations as active_borrowings' => function ($q) {
                    $q->where('circulation.status', 'ISSUED');
                },
                // Pending fines
                'fines as pending_fines' => function ($q) {
                    $q->where('fines.status', 'PENDING');
                }
            ]);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('members.full_name', 'like', "%{$search}%")
                  ->orWhere('members.email', 'like', "%{$search}%")
                  ->orWhere('members.phone', 'like', "%{$search}%")
                  ->orWhere('members.id', 'like', "%{$search}%");
            });
        }

        // Filter by member type
        if ($request->filled('member_type')) {
            $query->where('members.member_type', $request->member_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('members.status', $request->status);
        }

        $members = $query->paginate(20);

        $stats = [
            'total_members'   => Member::count(),
            'active_members'  => Member::where('members.status', 'ACTIVE')->count(),
            'student_count'   => Member::where('members.member_type', 'STUDENT')->count(),
            'faculty_count'   => Member::where('members.member_type', 'FACULTY')->count(),
        ];

        return view('members.index', compact('members', 'stats'));
    }

    /* =======================
       CREATE
    ======================== */
    public function create()
    {
        return view('members.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'hrms_user_id' => 'required|integer|unique:members',
            'full_name'    => 'required|string|max:100',
            'member_type'  => 'required|in:STUDENT,FACULTY',
            'email'        => 'required|email|max:100|unique:members',
            'phone'        => 'nullable|string|max:15',
            'borrow_limit' => 'required|integer|min:1|max:10',
            'status'       => 'required|in:ACTIVE,INACTIVE',
        ]);

        $member = Member::create($validated);

        AuditLog::create([
            'action_type'  => 'MEMBER_CREATE',
            'description'  => "Member registered: {$member->full_name}",
            'performed_by' => auth()->id(),
        ]);

        return redirect()->route('members.show', $member->id)
            ->with('success', 'Member registered successfully');
    }

    /* =======================
       SHOW MEMBER
    ======================== */
    public function show($id)
    {
        $member = Member::with([
            'circulations.copy.book',
            'fines' => fn ($q) => $q->where('fines.status', 'PENDING')
        ])->findOrFail($id);

        $member->totalBorrowings = $member->circulations()->count();

        $member->overdueLoans = $member->circulations()
            ->where('circulation.status', 'ISSUED')
            ->where('circulation.due_date', '<', now())
            ->count();

        return view('members.show', compact('member'));
    }

    /* =======================
       EDIT / UPDATE
    ======================== */
    public function edit($id)
    {
        return view('members.edit', [
            'member' => Member::findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $member = Member::findOrFail($id);

        $validated = $request->validate([
            'hrms_user_id' => 'required|integer|unique:members,hrms_user_id,' . $id,
            'full_name'    => 'required|string|max:100',
            'member_type'  => 'required|in:STUDENT,FACULTY',
            'email'        => 'required|email|max:100|unique:members,email,' . $id,
            'phone'        => 'nullable|string|max:15',
            'borrow_limit' => 'required|integer|min:1|max:10',
            'status'       => 'required|in:ACTIVE,INACTIVE',
        ]);

        $member->update($validated);

        AuditLog::create([
            'action_type'  => 'MEMBER_UPDATE',
            'description'  => "Member updated: {$member->full_name}",
            'performed_by' => auth()->id(),
        ]);

        return redirect()->route('members.show', $member->id)
            ->with('success', 'Member updated successfully');
    }

    /* =======================
       DELETE
    ======================== */
    public function destroy($id)
    {
        $member = Member::findOrFail($id);

        if ($member->circulations()
            ->where('circulation.status', 'ISSUED')
            ->exists()) {
            return back()->with('error', 'Cannot delete member with active borrowings');
        }

        $member->delete();

        AuditLog::create([
            'action_type'  => 'MEMBER_DELETE',
            'description'  => "Member deleted: {$member->full_name}",
            'performed_by' => auth()->id(),
        ]);

        return redirect()->route('members.index')
            ->with('success', 'Member deleted successfully');
    }

    /* =======================
       SEARCH
    ======================== */
    public function search(Request $request)
{
    $q = $request->q;

    $members = Member::where('full_name', 'like', "%$q%")
        ->orWhere('email', 'like', "%$q%")
        ->orWhere('phone', 'like', "%$q%")
        ->limit(10)
        ->get();

    return response()->json(
        $members->map(function ($m) {
            return [
                'id' => $m->id,
                'full_name' => $m->full_name,
                'email' => $m->email ?? '',
                'phone' => $m->phone ?? '',
                'member_type' => $m->member_type ?? 'STUDENT',
                'borrow_limit' => $m->borrow_limit ?? 3,
                'status' => $m->status ?? 'ACTIVE',
            ];
        })
    );
}

/* =======================
       STATS
    ======================== */
    public function stats(Request $request)
{
    $member = Member::findOrFail($request->member_id);

    // SAFE count (no relationship dependency)
    $currentBorrowings = \DB::table('circulations')
        ->where('member_id', $member->id)
        ->whereNull('returned_at')
        ->count();

    return response()->json([
        'full_name' => $member->full_name,
        'email' => $member->email ?? '',
        'member_type' => $member->member_type ?? 'STUDENT',
        'borrow_limit' => $member->borrow_limit ?? 3,
        'current_borrowings' => $currentBorrowings,
        'pending_fines' => 0,
        'status' => $member->status ?? 'ACTIVE',
        'has_overdue' => false,
    ]);
}




    /* =======================
       STATUS TOGGLES
    ======================== */
    public function activate($id)
    {
        Member::where('id', $id)->update(['status' => 'ACTIVE']);
        return back()->with('success', 'Member activated');
    }

    public function deactivate($id)
    {
        Member::where('id', $id)->update(['status' => 'INACTIVE']);
        return back()->with('success', 'Member deactivated');
    }
}
