@extends('layouts.app')

@section('title', 'Circulation Report')
@section('subtitle', 'Book issue and return statistics')

@section('header-buttons')
    <div class="btn-group">
        <button class="btn btn-outline-primary" onclick="printReport()">
            <i class="bi bi-printer me-1"></i> Print
        </button>
        <button class="btn btn-outline-success" onclick="exportReport()">
            <i class="bi bi-download me-1"></i> Export
        </button>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
@endsection

@section('content')
<!-- Report Filters -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="{{ route('reports.circulation') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" 
                       value="{{ request('start_date', date('Y-m-01')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" 
                       value="{{ request('end_date', date('Y-m-t')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Member Type</label>
                <select name="member_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="STUDENT" {{ request('member_type') == 'STUDENT' ? 'selected' : '' }}>Students</option>
                    <option value="FACULTY" {{ request('member_type') == 'FACULTY' ? 'selected' : '' }}>Faculty</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter me-1"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-white-50">Total Issues</h6>
                        <h2 class="mb-0">{{ $summary['total_issues'] }}</h2>
                    </div>
                    <i class="bi bi-arrow-up-right fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-white-50">Total Returns</h6>
                        <h2 class="mb-0">{{ $summary['total_returns'] }}</h2>
                    </div>
                    <i class="bi bi-arrow-down-left fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-white-50">Unique Members</h6>
                        <h2 class="mb-0">{{ $summary['unique_members'] }}</h2>
                    </div>
                    <i class="bi bi-people fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-white-50">Unique Books</h6>
                        <h2 class="mb-0">{{ $summary['unique_books'] }}</h2>
                    </div>
                    <i class="bi bi-book fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Circulation Details -->
<div class="card shadow">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">Circulation Details</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Transaction ID</th>
                        <th>Member</th>
                        <th>Book</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($circulations as $circulation)
                    <tr>
                        <td>{{ $circulation->issue_date->format('M d, Y') }}</td>
                        <td>
                            <span class="badge bg-secondary">#{{ $circulation->transaction_id }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-1 me-2">
                                    <i class="bi bi-person text-primary"></i>
                                </div>
                                <div>
                                    <strong>{{ $circulation->member->full_name }}</strong><br>
                                    <small class="text-muted">{{ $circulation->member->member_type }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded p-1 me-2">
                                    <i class="bi bi-book text-primary"></i>
                                </div>
                                <div>
                                    <strong>{{ $circulation->copy->book->title }}</strong><br>
                                    <small class="text-muted">{{ $circulation->copy->book->author }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $circulation->issue_date->format('M d, Y') }}</td>
                        <td>
                            <span class="badge bg-{{ $circulation->due_date->isPast() ? 'danger' : 'success' }}">
                                {{ $circulation->due_date->format('M d, Y') }}
                            </span>
                        </td>
                        <td>
                            @if($circulation->return_date)
                                <span class="badge bg-success">{{ $circulation->return_date->format('M d, Y') }}</span>
                            @else
                                <span class="badge bg-warning">Not Returned</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $circulation->status == 'RETURNED' ? 'success' : ($circulation->status == 'OVERDUE' ? 'danger' : 'warning') }}">
                                {{ $circulation->status }}
                            </span>
                        </td>
                        <td>
                            @if($circulation->return_date)
                                {{ $circulation->issue_date->diffInDays($circulation->return_date) }} days
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Charts and Analysis -->
<div class="row mt-4">
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Daily Circulation Trend</h5>
            </div>
            <div class="card-body">
                <div class="text-center py-5">
                    <i class="bi bi-bar-chart fs-1 text-muted"></i>
                    <p class="mt-2 text-muted">Chart would display daily issue/return trends</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Member Type Distribution</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Member Type</th>
                            <th>Issues</th>
                            <th>Percentage</th>
                            <th>Avg. Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Students</td>
                            <td>{{ $stats['student_issues'] }}</td>
                            <td>{{ $stats['student_percentage'] }}%</td>
                            <td>{{ $stats['student_avg_duration'] }} days</td>
                        </tr>
                        <tr>
                            <td>Faculty</td>
                            <td>{{ $stats['faculty_issues'] }}</td>
                            <td>{{ $stats['faculty_percentage'] }}%</td>
                            <td>{{ $stats['faculty_avg_duration'] }} days</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Top Books -->
<div class="card shadow mt-4">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">Most Circulated Books</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Book</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Issues</th>
                        <th>Avg. Duration</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($top_books as $index => $book)
                    <tr>
                        <td>
                            <span class="badge bg-{{ $index < 3 ? 'primary' : 'secondary' }}">
                                #{{ $index + 1 }}
                            </span>
                        </td>
                        <td>{{ $book->title }}</td>
                        <td>{{ $book->author }}</td>
                        <td>{{ $book->category }}</td>
                        <td>{{ $book->issue_count }}</td>
                        <td>{{ $book->avg_duration }} days</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Top Members -->
<div class="card shadow mt-4">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">Most Active Members</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Member</th>
                        <th>Type</th>
                        <th>Books Issued</th>
                        <th>Avg. Return Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($top_members as $index => $member)
                    <tr>
                        <td>
                            <span class="badge bg-{{ $index < 3 ? 'success' : 'secondary' }}">
                                #{{ $index + 1 }}
                            </span>
                        </td>
                        <td>{{ $member->full_name }}</td>
                        <td>{{ $member->member_type }}</td>
                        <td>{{ $member->issue_count }}</td>
                        <td>{{ $member->avg_return_days }} days</td>
                        <td>
                            <span class="badge bg-{{ $member->status == 'ACTIVE' ? 'success' : 'danger' }}">
                                {{ $member->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function printReport() {
    window.print();
}

function exportReport() {
    // Implement export functionality
    alert('Export functionality would be implemented here');
}

// Initialize date pickers
$(document).ready(function() {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    if(!$('input[name="start_date"]').val()) {
        $('input[name="start_date"]').val(firstDay.toISOString().split('T')[0]);
    }
    
    if(!$('input[name="end_date"]').val()) {
        $('input[name="end_date"]').val(lastDay.toISOString().split('T')[0]);
    }
});
</script>
@endpush