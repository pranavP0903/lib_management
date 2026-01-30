<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Circulation;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    // Display all members
    public function index(Request $request)
    {
        $query = Member::withCount(['activeBorrowings', 'fines as pending_fines' => function($q) {
            $q->where('status', 'PENDING');
        }]);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        // Filter by member type
        if ($request->has('member_type')) {
            $query->where('member_type', $request->member_type);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $members = $query->paginate(20);
        
        $stats = [
            'total_members' => Member::count(),
            'active_members' => Member::where('status', 'ACTIVE')->count(),
            'student_count' => Member::where('member_type', 'STUDENT')->count(),
            'faculty_count' => Member::where('member_type', 'FACULTY')->count(),
        ];

        return view('members.index', compact('members', 'stats'));
    }

    // Show create member form
    public function create()
    {
        return view('members.create');
    }

    // Store new member
    public function store(Request $request)
    {
        $validated = $request->validate([
            'hrms_user_id' => 'required|integer|unique:members',
            'full_name' => 'required|string|max:100',
            'member_type' => 'required|in:STUDENT,FACULTY',
            'email' => 'required|email|max:100|unique:members',
            'phone' => 'nullable|string|max:15',
            'borrow_limit' => 'required|integer|min:1|max:10',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        $member = Member::create($validated);

        AuditLog::create([
            'action_type' => 'MEMBER_CREATE',
            'description' => "Member registered: {$member->full_name}",
            'performed_by' => auth()->id() ?? null,
        ]);

        return redirect()->route('members.show', $member->id)
            ->with('success', 'Member registered successfully');
    }

    // Display single member
    public function show($id)
    {
        $member = Member::with([
            'activeBorrowings.copy.book',
            'circulations.copy.book' => function($q) {
                $q->orderBy('issue_date', 'desc')->limit(10);
            },
            'fines' => function($q) {
                $q->where('status', 'PENDING');
            }
        ])->findOrFail($id);

        // Calculate statistics
        $member->totalBorrowings = $member->circulations()->count();
        $member->overdueLoans = $member->circulations()
            ->where('status', 'ISSUED')
            ->where('due_date', '<', now())
            ->count();

        return view('members.show', compact('member'));
    }

    // Show edit member form
    public function edit($id)
    {
        $member = Member::findOrFail($id);
        return view('members.edit', compact('member'));
    }

    // Update member
    public function update(Request $request, $id)
    {
        $member = Member::findOrFail($id);

        $validated = $request->validate([
            'hrms_user_id' => 'required|integer|unique:members,hrms_user_id,' . $id . ',id',
            'full_name' => 'required|string|max:100',
            'member_type' => 'required|in:STUDENT,FACULTY',
            'email' => 'required|email|max:100|unique:members,email,' . $id . ',id',
            'phone' => 'nullable|string|max:15',
            'borrow_limit' => 'required|integer|min:1|max:10',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        $member->update($validated);

        AuditLog::create([
            'action_type' => 'MEMBER_UPDATE',
            'description' => "Member updated: {$member->full_name}",
            'performed_by' => auth()->id() ?? null,
        ]);

        return redirect()->route('members.show', $member->id)
            ->with('success', 'Member updated successfully');
    }

    // Delete member
    public function destroy($id)
    {
        $member = Member::findOrFail($id);
        $name = $member->full_name;
        
        // Check if member has active borrowings
        if ($member->activeBorrowings()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete member with active borrowings');
        }

        $member->delete();

        AuditLog::create([
            'action_type' => 'MEMBER_DELETE',
            'description' => "Member deleted: {$name}",
            'performed_by' => auth()->id() ?? null,
        ]);

        return redirect()->route('members.index')
            ->with('success', 'Member deleted successfully');
    }

    // Activate member
    public function activate($id)
    {
        $member = Member::findOrFail($id);
        $member->update(['status' => 'ACTIVE']);

        AuditLog::create([
            'action_type' => 'MEMBER_ACTIVATE',
            'description' => "Member activated: {$member->full_name}",
            'performed_by' => auth()->id() ?? null,
        ]);

        return redirect()->back()
            ->with('success', 'Member activated successfully');
    }

    // Deactivate member
    public function deactivate($id)
    {
        $member = Member::findOrFail($id);
        $member->update(['status' => 'INACTIVE']);

        AuditLog::create([
            'action_type' => 'MEMBER_DEACTIVATE',
            'description' => "Member deactivated: {$member->full_name}",
            'performed_by' => auth()->id() ?? null,
        ]);

        return redirect()->back()
            ->with('success', 'Member deactivated successfully');
    }

    // AJAX search for members
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        $members = Member::where('full_name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('id', 'like', "%{$query}%")
            ->limit(10)
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'full_name' => $member->full_name,
                    'email' => $member->email,
                    'member_type' => $member->member_type,
                    'borrow_limit' => $member->borrow_limit,
                    'status' => $member->status
                ];
            });

        return response()->json($members);
    }

    // Get member statistics
    public function stats(Request $request)
    {
        $memberId = $request->get('member_id');
        $member = Member::findOrFail($memberId);

        $stats = [
            'full_name' => $member->full_name,
            'email' => $member->email,
            'member_type' => $member->member_type,
            'status' => $member->status,
            'current_loans' => $member->activeBorrowings()->count(),
            'borrow_limit' => $member->borrow_limit,
            'pending_fines' => $member->fines()->where('status', 'PENDING')->sum('fine_amount'),
            'has_overdue' => $member->circulations()
                ->where('status', 'ISSUED')
                ->where('due_date', '<', now())
                ->exists(),
        ];

        return response()->json($stats);
    }
}