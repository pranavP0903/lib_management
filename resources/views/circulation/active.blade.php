@extends('layouts.app')

@section('title', 'Active Borrowings')
@section('subtitle', 'Currently issued books')

@section('header-buttons')
    <a href="{{ route('circulation.issue') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Issue New Book
    </a>
@endsection

@section('content')
<!-- Filters -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="{{ route('circulation.active') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <select name="member_type" class="form-select">
                    <option value="">All Member Types</option>
                    <option value="STUDENT" {{ request('member_type') == 'STUDENT' ? 'selected' : '' }}>Students</option>
                    <option value="FACULTY" {{ request('member_type') == 'FACULTY' ? 'selected' : '' }}>Faculty</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="overdue" class="form-select">
                    <option value="">All Borrowings</option>
                    <option value="overdue" {{ request('overdue') == 'overdue' ? 'selected' : '' }}>Overdue Only</option>
                    <option value="due_soon" {{ request('overdue') == 'due_soon' ? 'selected' : '' }}>Due Soon (3 days)</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="due_date" class="form-control" 
                       value="{{ request('due_date') }}" placeholder="Due Date">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Active Borrowings Table -->
<div class="card shadow">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Active Borrowings ({{ $borrowings->count() }})</h5>
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-primary" onclick="printTable()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <button class="btn btn-sm btn-outline-success" onclick="exportToCSV()">
                <i class="bi bi-file-earmark-arrow-down me-1"></i> Export
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable" id="borrowingsTable">
                <thead>
                    <tr>
                        <th>Borrowing ID</th>
                        <th>Member</th>
                        <th>Book</th>
                        <th>Copy</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Days Left</th>
                        <th>Fine</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($borrowings as $borrowing)
                    <tr class="{{ $borrowing->is_overdue ? 'table-danger' : ($borrowing->is_due_soon ? 'table-warning' : '') }}">
                        <td>
                            <span class="badge bg-secondary">#{{ $borrowing->id }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-1 me-2">
                                    <i class="bi bi-person text-primary"></i>
                                </div>
                                <div>
                                    <strong>{{ $borrowing->member->full_name }}</strong><br>
                                    <small class="text-muted">
                                        {{ $borrowing->member->member_type }}
                                        @if($borrowing->member->phone)
                                        • {{ $borrowing->member->phone }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded p-1 me-2">
                                    <i class="bi bi-book text-primary"></i>
                                </div>
                                <div>
                                    <strong>{{ $borrowing->copy->book->title }}</strong><br>
                                    <small class="text-muted">{{ $borrowing->copy->book->author }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info">Copy #{{ $borrowing->copy->copy_number }}</span>
                        </td>
                        <td>{{ $borrowing->issue_date->format('M d, Y') }}</td>
                        <td>
                            <span class="badge bg-{{ $borrowing->is_overdue ? 'danger' : ($borrowing->is_due_soon ? 'warning' : 'success') }}">
                                {{ $borrowing->due_date->format('M d, Y') }}
                            </span>
                        </td>
                        <td>
                            @if($borrowing->is_overdue)
                                <span class="text-danger">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Overdue by {{ $borrowing->days_overdue }} days
                                </span>
                            @else
                                <span class="text-success">{{ $borrowing->days_remaining }} days left</span>
                            @endif
                        </td>
                        <td>
                            @if($borrowing->is_overdue)
                                <span class="badge bg-danger">₹{{ $borrowing->calculated_fine }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <form action="{{ route('circulation.return.submit') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="circulation_id" value="{{ $borrowing->id }}">
                                    <button type="submit" class="btn btn-outline-primary" title="Return">
                                        <i class="bi bi-arrow-down-left"></i>
                                    </button>
                                </form>
                                @if(!$borrowing->is_overdue)
                                <form action="{{ route('circulation.renew', $borrowing->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success" title="Renew">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </form>
                                @endif
                                <button type="button" class="btn btn-outline-info" 
                                        data-bs-toggle="modal" data-bs-target="#borrowingDetails{{ $borrowing->id }}"
                                        title="Details">
                                    <i class="bi bi-info-circle"></i>
                                </button>
                            </div>
                            
                            <!-- Borrowing Details Modal -->
                            <div class="modal fade" id="borrowingDetails{{ $borrowing->id }}">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Borrowing Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6>Member Information</h6>
                                                    <p class="mb-1"><strong>Name:</strong> {{ $borrowing->member->full_name }}</p>
                                                    <p class="mb-1"><strong>Type:</strong> {{ $borrowing->member->member_type }}</p>
                                                    <p class="mb-1"><strong>Email:</strong> {{ $borrowing->member->email }}</p>
                                                    <p class="mb-1"><strong>Phone:</strong> {{ $borrowing->member->phone ?? 'N/A' }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6>Book Information</h6>
                                                    <p class="mb-1"><strong>Title:</strong> {{ $borrowing->copy->book->title }}</p>
                                                    <p class="mb-1"><strong>Author:</strong> {{ $borrowing->copy->book->author }}</p>
                                                    <p class="mb-1"><strong>ISBN:</strong> {{ $borrowing->copy->book->isbn }}</p>
                                                    <p class="mb-1"><strong>Copy:</strong> #{{ $borrowing->copy->copy_number }}</p>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Issue Date:</strong> {{ $borrowing->issue_date->format('M d, Y') }}</p>
                                                    <p><strong>Due Date:</strong> {{ $borrowing->due_date->format('M d, Y') }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    @if($borrowing->is_overdue)
                                                    <div class="alert alert-danger">
                                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                                        Overdue by {{ $borrowing->days_overdue }} days
                                                        <br>
                                                        <strong>Fine:</strong> ₹{{ $borrowing->calculated_fine }}
                                                    </div>
                                                    @elseif($borrowing->is_due_soon)
                                                    <div class="alert alert-warning">
                                                        <i class="bi bi-clock me-2"></i>
                                                        Due in {{ $borrowing->days_remaining }} days
                                                    </div>
                                                    @else
                                                    <div class="alert alert-success">
                                                        <i class="bi bi-check-circle me-2"></i>
                                                        {{ $borrowing->days_remaining }} days remaining
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <form action="{{ route('circulation.return.submit') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="circulation_id" value="{{ $borrowing->id }}">
                                                <button type="submit" class="btn btn-primary">Return Book</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-white-50">Total Active Borrowings</h6>
                        <h2 class="mb-0">{{ $stats['total'] }}</h2>
                    </div>
                    <i class="bi bi-book fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-white-50">Overdue Borrowings</h6>
                        <h2 class="mb-0">{{ $stats['overdue'] }}</h2>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-white-50">Due Today</h6>
                        <h2 class="mb-0">{{ $stats['due_today'] }}</h2>
                    </div>
                    <i class="bi bi-calendar-day fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions -->
<div class="card shadow mt-4">
    <div class="card-header bg-white">
        <h6 class="card-title mb-0">Bulk Actions</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <button class="btn btn-outline-primary w-100" onclick="sendReminders()">
                    <i class="bi bi-bell me-1"></i> Send Due Reminders
                </button>
            </div>
            <div class="col-md-4">
                <button class="btn btn-outline-success w-100" onclick="markMultipleReturned()">
                    <i class="bi bi-check-circle me-1"></i> Mark Selected as Returned
                </button>
            </div>
            <div class="col-md-4">
                <button class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#bulkFineModal">
                    <i class="bi bi-cash-coin me-1"></i> Apply Bulk Fines
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function printTable() {
    window.print();
}

function exportToCSV() {
    alert('CSV export functionality would be implemented here');
}

function sendReminders() {
    alert('Reminders functionality would be implemented here');
}

function markMultipleReturned() {
    const selectedBorrowings = [];
    $('input[name="borrowing_select"]:checked').each(function() {
        selectedBorrowings.push($(this).val());
    });
    
    if(selectedBorrowings.length === 0) {
        alert('Please select borrowings to mark as returned');
        return;
    }
    
    alert('Bulk return functionality would be implemented here');
}
</script>
@endpush