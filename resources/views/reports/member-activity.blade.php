@extends('layouts.app')

@section('title', 'Member Activity Report')
@section('subtitle', 'Member borrowing patterns and statistics')

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
        <form action="{{ route('reports.member-activity') }}" method="GET" class="row g-3">
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
                <label class="form-label">Activity Level</label>
                <select name="activity_level" class="form-select">
                    <option value="">All Levels</option>
                    <option value="high" {{ request('activity_level') == 'high' ? 'selected' : '' }}>High Activity (5+ books)</option>
                    <option value="medium" {{ request('activity_level') == 'medium' ? 'selected' : '' }}>Medium Activity (2-4 books)</option>
                    <option value="low" {{ request('activity_level') == 'low' ? 'selected' : '' }}>Low Activity (0-1 books)</option>
                </select>
            </div>
            <div class="col-md-12 mt-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter me-1"></i> Apply Filters
                </button>
                <a href="{{ route('reports.member-activity') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise me-1"></i> Reset
                </a>
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
                        <h6 class="card-title text-white-50">Total Members</h6>
                        <h2 class="mb-0">{{ $summary['total_members'] }}</h2>
                    </div>
                    <i class="bi bi-people fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-white-50">Active Members</h6>
                        <h2 class="mb-0">{{ $summary['active_members'] }}</h2>
                    </div>
                    <i class="bi bi-person-check fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-white-50">Total Borrowings</h6>
                        <h2 class="mb-0">{{ $summary['total_borrowings'] }}</h2>
                    </div>
                    <i class="bi bi-book fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-white-50">Avg. Borrowings/Member</h6>
                        <h2 class="mb-0">{{ $summary['avg_borrowings_per_member'] }}</h2>
                    </div>
                    <i class="bi bi-graph-up fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Member Activity Table -->
<div class="card shadow">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">Member Activity Details</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Member ID</th>
                        <th>Member Name</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Total Borrowings</th>
                        <th>Active Borrowings</th>
                        <th>Overdue</th>
                        <th>Fines Paid</th>
                        <th>Avg. Return Time</th>
                        <th>Activity Score</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($members as $member)
                    <tr>
                        <td>
                            <span class="badge bg-secondary">#{{ $member->member_id }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-1 me-2">
                                    <i class="bi bi-person text-primary"></i>
                                </div>
                                <div>
                                    <strong>{{ $member->full_name }}</strong><br>
                                    <small class="text-muted">{{ $member->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $member->member_type == 'FACULTY' ? 'info' : 'primary' }}">
                                {{ $member->member_type }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $member->status == 'ACTIVE' ? 'success' : 'danger' }}">
                                {{ $member->status }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $member->total_borrowings }}</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $member->active_borrowings >= $member->borrow_limit ? 'danger' : 'warning' }}">
                                {{ $member->active_borrowings }}/{{ $member->borrow_limit }}
                            </span>
                        </td>
                        <td>
                            @if($member->overdue_count > 0)
                                <span class="badge bg-danger">{{ $member->overdue_count }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-success">₹{{ $member->fines_paid }}</span>
                        </td>
                        <td>
                            @if($member->avg_return_days > 0)
                                <span class="badge bg-{{ $member->avg_return_days > 10 ? 'warning' : 'success' }}">
                                    {{ $member->avg_return_days }} days
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $activityScore = $member->activity_score;
                                $scoreClass = $activityScore >= 80 ? 'success' : ($activityScore >= 60 ? 'warning' : 'danger');
                            @endphp
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-{{ $scoreClass }}" 
                                     role="progressbar" 
                                     style="width: {{ $activityScore }}%">
                                    {{ $activityScore }}%
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('members.show', $member->member_id) }}" 
                                   class="btn btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('reports.member-detail', $member->member_id) }}" 
                                   class="btn btn-outline-primary" title="Report">
                                    <i class="bi bi-file-text"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Activity Analysis -->
<div class="row mt-4">
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Activity by Member Type</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Member Type</th>
                            <th>Total Members</th>
                            <th>Active Members</th>
                            <th>Total Borrowings</th>
                            <th>Avg. Borrowings/Member</th>
                            <th>Overdue %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Students</td>
                            <td>{{ $type_stats['students']['total'] }}</td>
                            <td>{{ $type_stats['students']['active'] }}</td>
                            <td>{{ $type_stats['students']['borrowings'] }}</td>
                            <td>{{ $type_stats['students']['avg_borrowings'] }}</td>
                            <td>{{ $type_stats['students']['overdue_percentage'] }}%</td>
                        </tr>
                        <tr>
                            <td>Faculty</td>
                            <td>{{ $type_stats['faculty']['total'] }}</td>
                            <td>{{ $type_stats['faculty']['active'] }}</td>
                            <td>{{ $type_stats['faculty']['borrowings'] }}</td>
                            <td>{{ $type_stats['faculty']['avg_borrowings'] }}</td>
                            <td>{{ $type_stats['faculty']['overdue_percentage'] }}%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Top Performers</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Member</th>
                                <th>Borrowings</th>
                                <th>On-time Returns</th>
                                <th>Activity Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($top_performers as $index => $member)
                            <tr>
                                <td>
                                    <span class="badge bg-{{ $index < 3 ? 'success' : 'secondary' }}">
                                        #{{ $index + 1 }}
                                    </span>
                                </td>
                                <td>{{ $member->full_name }}</td>
                                <td>{{ $member->total_borrowings }}</td>
                                <td>{{ $member->ontime_returns }}%</td>
                                <td>
                                    <span class="badge bg-success">{{ $member->activity_score }}%</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inactive Members -->
<div class="card shadow mt-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Inactive Members (No borrowings in last 30 days)</h5>
        <span class="badge bg-danger">{{ $inactive_members->count() }}</span>
    </div>
    <div class="card-body">
        @if($inactive_members->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Type</th>
                        <th>Last Activity</th>
                        <th>Total Borrowings</th>
                        <th>Days Inactive</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inactive_members as $member)
                    <tr>
                        <td>{{ $member->full_name }}</td>
                        <td>{{ $member->member_type }}</td>
                        <td>
                            @if($member->last_activity)
                                {{ $member->last_activity->format('M d, Y') }}
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </td>
                        <td>{{ $member->total_borrowings }}</td>
                        <td>
                            <span class="badge bg-warning">{{ $member->days_inactive }} days</span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" 
                                    onclick="sendReactivationEmail({{ $member->member_id }})">
                                <i class="bi bi-envelope"></i> Notify
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-4">
            <i class="bi bi-check-circle fs-1 text-success"></i>
            <p class="mt-2">All members have been active recently</p>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function printReport() {
    window.print();
}

function exportReport() {
    alert('Export functionality would be implemented here');
}

function sendReactivationEmail(memberId) {
    if(confirm('Send reactivation email to this member?')) {
        $.ajax({
            url: '{{ route("members.send-reactivation") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                member_id: memberId
            },
            success: function(response) {
                alert('Email sent successfully');
            }
        });
    }
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