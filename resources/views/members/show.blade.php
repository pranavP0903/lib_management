@extends('layouts.app')

@section('title', $member->full_name)

@section('header-buttons')
    <div class="btn-group">
        <a href="{{ route('members.edit', $member->member_id) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('circulation.issue') }}?member_id={{ $member->member_id }}" class="btn btn-primary">
            <i class="bi bi-book me-1"></i> Issue Book
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4">
        <!-- Member Profile Card -->
        <div class="card shadow mb-4">
            <div class="card-body text-center">
                <!-- Profile Picture/Icon -->
                <div class="mb-4">
                    <div class="bg-{{ $member->member_type == 'FACULTY' ? 'info' : 'primary' }} text-white rounded-circle p-4 mx-auto d-inline-block">
                        <i class="bi bi-{{ $member->member_type == 'FACULTY' ? 'person-workspace' : 'mortarboard' }} fs-1"></i>
                    </div>
                </div>
                
                <!-- Member Info -->
                <h4 class="mb-2">{{ $member->full_name }}</h4>
                <p class="text-muted mb-3">
                    <span class="badge bg-{{ $member->member_type == 'FACULTY' ? 'info' : 'primary' }}">
                        {{ $member->member_type }}
                    </span>
                    <span class="badge bg-{{ $member->status == 'ACTIVE' ? 'success' : 'danger' }} ms-2">
                        {{ $member->status }}
                    </span>
                </p>
                
                <!-- Contact Info -->
                <div class="mb-4">
                    <p class="mb-2">
                        <i class="bi bi-envelope me-2 text-muted"></i>
                        {{ $member->email }}
                    </p>
                    @if($member->phone)
                    <p class="mb-2">
                        <i class="bi bi-telephone me-2 text-muted"></i>
                        {{ $member->phone }}
                    </p>
                    @endif
                    <p class="mb-0">
                        <i class="bi bi-person-badge me-2 text-muted"></i>
                        HRMS ID: {{ $member->hrms_user_id }}
                    </p>
                </div>
                
                <!-- Quick Actions -->
                <div class="d-grid gap-2">
                    <a href="{{ route('circulation.issue') }}?member_id={{ $member->member_id }}" class="btn btn-primary">
                        <i class="bi bi-book-plus me-1"></i> Issue Book
                    </a>
                    @if($member->status == 'ACTIVE')
                    <form action="{{ route('members.deactivate', $member->member_id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-outline-warning w-100">
                            <i class="bi bi-person-x me-1"></i> Deactivate
                        </button>
                    </form>
                    @else
                    <form action="{{ route('members.activate', $member->member_id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-outline-success w-100">
                            <i class="bi bi-person-check me-1"></i> Activate
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            <div class="card-footer bg-white">
                <small class="text-muted">
                    <i class="bi bi-calendar me-1"></i>
                    Member since {{ $member->created_at->format('M d, Y') }}
                </small>
            </div>
        </div>
        
        <!-- Borrowing Stats -->
        <div class="card shadow">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">Borrowing Statistics</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Current Borrowings:</span>
                        <span class="badge bg-{{ $member->activeBorrowings->count() >= $member->borrow_limit ? 'danger' : 'warning' }}">
                            {{ $member->activeBorrowings->count() }}/{{ $member->borrow_limit }}
                        </span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-warning" role="progressbar" 
                             style="width: {{ ($member->activeBorrowings->count() / $member->borrow_limit) * 100 }}%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Pending Fines:</span>
                        <span class="badge bg-danger">₹{{ $member->pendingFines }}</span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Borrowings:</span>
                        <span class="badge bg-info">{{ $member->totalBorrowings }}</span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Overdue Books:</span>
                        <span class="badge bg-danger">{{ $member->overdueBorrowings->count() }}</span>
                    </div>
                </div>
                
                <div class="mb-0">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Avg. Return Time:</span>
                        <span class="badge bg-success">{{ $member->avgReturnDays }} days</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <!-- Active Borrowings -->
        <div class="card shadow mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Active Borrowings</h5>
                <span class="badge bg-warning">{{ $member->activeBorrowings->count() }} active</span>
            </div>
            <div class="card-body">
                @if($member->activeBorrowings->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Book</th>
                                <th>Issued On</th>
                                <th>Due Date</th>
                                <th>Days Left</th>
                                <th>Fine</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($member->activeBorrowings as $borrowing)
                            <tr class="{{ $borrowing->isOverdue ? 'table-danger' : '' }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded p-1 me-2">
                                            <i class="bi bi-book text-primary"></i>
                                        </div>
                                        <div>
                                            <strong>{{ $borrowing->copy->book->title }}</strong><br>
                                            <small class="text-muted">Copy #{{ $borrowing->copy->copy_number }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $borrowing->issue_date->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $borrowing->isOverdue ? 'danger' : 'warning' }}">
                                        {{ $borrowing->due_date->format('M d, Y') }}
                                    </span>
                                </td>
                                <td>
                                    @if($borrowing->isOverdue)
                                        <span class="text-danger">Overdue by {{ $borrowing->overdueDays }} days</span>
                                    @else
                                        <span class="text-success">{{ $borrowing->daysRemaining }} days left</span>
                                    @endif
                                </td>
                                <td>
                                    @if($borrowing->isOverdue)
                                        <span class="badge bg-danger">₹{{ $borrowing->calculatedFine }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <form action="{{ route('circulation.return.submit') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="transaction_id" value="{{ $borrowing->transaction_id }}">
                                            <button type="submit" class="btn btn-outline-primary" title="Return">
                                                <i class="bi bi-arrow-down-left"></i>
                                            </button>
                                        </form>
                                        @if(!$borrowing->isOverdue)
                                        <form action="{{ route('circulation.renew', $borrowing->transaction_id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Renew">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-check-circle fs-1 text-success"></i>
                    <p class="mt-2">No active borrowings</p>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Borrowing History -->
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Borrowing History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Book</th>
                                <th>Issued</th>
                                <th>Returned</th>
                                <th>Duration</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($member->circulations->sortByDesc('issue_date')->take(10) as $history)
                            <tr>
                                <td>{{ $history->issue_date->format('M d, Y') }}</td>
                                <td>{{ $history->copy->book->title }}</td>
                                <td>{{ $history->issue_date->format('M d') }}</td>
                                <td>
                                    @if($history->return_date)
                                        {{ $history->return_date->format('M d') }}
                                    @else
                                        <span class="text-warning">Not returned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($history->return_date)
                                        {{ $history->issue_date->diffInDays($history->return_date) }} days
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $history->status == 'RETURNED' ? 'success' : ($history->status == 'OVERDUE' ? 'danger' : 'warning') }}">
                                        {{ $history->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-clock-history fs-1 text-muted"></i>
                                    <p class="mt-2">No borrowing history yet</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($member->circulations->count() > 10)
                <div class="text-center mt-3">
                    <a href="#" class="btn btn-sm btn-outline-primary">View Full History</a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Fines Modal -->
@if($member->pendingFines > 0)
<div class="modal fade" id="finesModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pending Fines</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    This member has pending fines of ₹{{ $member->pendingFines }}
                </div>
                
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Book</th>
                                <th>Overdue Days</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($member->fines as $fine)
                            @if($fine->fine_status == 'PENDING')
                            <tr>
                                <td>{{ $fine->circulation->copy->book->title }}</td>
                                <td>{{ $fine->overdue_days }} days</td>
                                <td>₹{{ $fine->fine_amount }}</td>
                                <td>
                                    <span class="badge bg-warning">{{ $fine->fine_status }}</span>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" 
                        onclick="markFinesPaid({{ $member->member_id }})">
                    Mark All as Paid
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function markFinesPaid(memberId) {
    if(confirm('Mark all fines as paid for this member?')) {
        // AJAX call to mark fines as paid
        $.ajax({
            url: '/fines/member/' + memberId + '/pay-all',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                location.reload();
            }
        });
    }
}
</script>
@endif
@endsection