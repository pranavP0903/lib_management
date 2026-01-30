@extends('layouts.app')

@section('title', 'Overdue Report')
@section('subtitle', 'Detailed analysis of overdue borrowings')

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
        <form action="{{ route('reports.overdue') }}" method="GET" class="row g-3">
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
                <label class="form-label">Overdue Period</label>
                <select name="overdue_period" class="form-select">
                    <option value="">All Periods</option>
                    <option value="1-7" {{ request('overdue_period') == '1-7' ? 'selected' : '' }}>1-7 Days</option>
                    <option value="8-14" {{ request('overdue_period') == '8-14' ? 'selected' : '' }}>8-14 Days</option>
                    <option value="15-30" {{ request('overdue_period') == '15-30' ? 'selected' : '' }}>15-30 Days</option>
                    <option value="30+" {{ request('overdue_period') == '30+' ? 'selected' : '' }}>30+ Days</option>
                </select>
            </div>
            <div class="col-md-12 mt-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter me-1"></i> Apply Filters
                </button>
                <a href="{{ route('reports.overdue') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-white-50">Total Overdue</h6>
                        <h2 class="mb-0">{{ $summary['total_overdue'] }}</h2>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-white-50">Total Fines</h6>
                        <h2 class="mb-0">₹{{ $summary['total_fines'] }}</h2>
                    </div>
                    <i class="bi bi-cash-coin fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-white-50">Avg. Overdue Days</h6>
                        <h2 class="mb-0">{{ $summary['avg_overdue_days'] }}</h2>
                    </div>
                    <i class="bi bi-calendar-x fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-white-50">Affected Members</h6>
                        <h2 class="mb-0">{{ $summary['affected_members'] }}</h2>
                    </div>
                    <i class="bi bi-people fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Overdue Details Table -->
<div class="card shadow">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Overdue Borrowing Details</h5>
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#applyFinesModal">
                <i class="bi bi-cash-coin me-1"></i> Apply Fines
            </button>
            <button class="btn btn-sm btn-outline-warning" onclick="sendReminders()">
                <i class="bi bi-bell me-1"></i> Send Reminders
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="selectAllOverdue">
                        </th>
                        <th>Transaction ID</th>
                        <th>Member</th>
                        <th>Book</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Overdue Days</th>
                        <th>Calculated Fine</th>
                        <th>Fine Applied</th>
                        <th>Contact Info</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($overdue_records as $record)
                    <tr>
                        <td>
                            <input type="checkbox" name="overdue_select" value="{{ $record->transaction_id }}" 
                                   class="overdue-checkbox">
                        </td>
                        <td>
                            <span class="badge bg-secondary">#{{ $record->transaction_id }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-1 me-2">
                                    <i class="bi bi-person text-primary"></i>
                                </div>
                                <div>
                                    <strong>{{ $record->member->full_name }}</strong><br>
                                    <small class="text-muted">
                                        {{ $record->member->member_type }}
                                        @if($record->member->phone)
                                        • {{ $record->member->phone }}
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
                                    <strong>{{ $record->copy->book->title }}</strong><br>
                                    <small class="text-muted">{{ $record->copy->book->author }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $record->issue_date->format('M d, Y') }}</td>
                        <td>
                            <span class="badge bg-danger">{{ $record->due_date->format('M d, Y') }}</span>
                            <br>
                            <small class="text-muted">{{ $record->due_date->diffForHumans() }}</small>
                        </td>
                        <td>
                            @php
                                $overdue_days = $record->overdue_days;
                                $badge_class = $overdue_days > 30 ? 'danger' : ($overdue_days > 15 ? 'warning' : 'info');
                            @endphp
                            <span class="badge bg-{{ $badge_class }}">
                                {{ $overdue_days }} days
                            </span>
                        </td>
                        <td>
                            <strong class="text-danger">₹{{ $record->calculated_fine }}</strong>
                        </td>
                        <td>
                            @if($record->fines->where('fine_status', 'PENDING')->count() > 0)
                                <span class="badge bg-warning">Pending</span>
                            @elseif($record->fines->where('fine_status', 'PAID')->count() > 0)
                                <span class="badge bg-success">Paid</span>
                            @else
                                <span class="badge bg-secondary">Not Applied</span>
                            @endif
                        </td>
                        <td>
                            <div class="small">
                                <div>
                                    <i class="bi bi-envelope me-1"></i>{{ $record->member->email }}
                                </div>
                                @if($record->member->phone)
                                <div>
                                    <i class="bi bi-telephone me-1"></i>{{ $record->member->phone }}
                                </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" 
                                        data-bs-toggle="modal" data-bs-target="#contactModal{{ $record->transaction_id }}"
                                        title="Contact">
                                    <i class="bi bi-telephone"></i>
                                </button>
                                @if($record->fines->where('fine_status', 'PENDING')->count() == 0)
                                <button type="button" class="btn btn-outline-warning" 
                                        onclick="applyFine({{ $record->transaction_id }})"
                                        title="Apply Fine">
                                    <i class="bi bi-cash-coin"></i>
                                </button>
                                @endif
                                <form action="{{ route('circulation.return.submit') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="transaction_id" value="{{ $record->transaction_id }}">
                                    <button type="submit" class="btn btn-outline-success" title="Mark Returned">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                </form>
                            </div>
                            
                            <!-- Contact Modal -->
                            <div class="modal fade" id="contactModal{{ $record->transaction_id }}">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Contact Member</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Message Template</label>
                                                <select class="form-select message-template" 
                                                        onchange="loadTemplate(this, {{ $record->transaction_id }})">
                                                    <option value="">Select Template</option>
                                                    <option value="gentle_reminder">Gentle Reminder</option>
                                                    <option value="warning">Warning Notice</option>
                                                    <option value="final_notice">Final Notice</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Message</label>
                                                <textarea class="form-control custom-message" rows="6" 
                                                          id="message{{ $record->transaction_id }}">
Dear {{ $record->member->full_name }},

This is a reminder that the following book is overdue:

Book: {{ $record->copy->book->title }}
Due Date: {{ $record->due_date->format('F j, Y') }}
Overdue Days: {{ $record->overdue_days }} days
Fine Amount: ₹{{ $record->calculated_fine }}

Please return the book immediately to avoid additional fines.

Thank you,
Library Management
                                                </textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="button" class="btn btn-primary" 
                                                    onclick="sendMessage({{ $record->transaction_id }})">
                                                <i class="bi bi-send me-1"></i> Send Message
                                            </button>
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

<!-- Overdue Analysis -->
<div class="row mt-4">
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Overdue Distribution by Member Type</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Member Type</th>
                            <th>Count</th>
                            <th>Percentage</th>
                            <th>Avg. Overdue Days</th>
                            <th>Total Fines</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Students</td>
                            <td>{{ $analysis['students']['count'] }}</td>
                            <td>{{ $analysis['students']['percentage'] }}%</td>
                            <td>{{ $analysis['students']['avg_days'] }} days</td>
                            <td>₹{{ $analysis['students']['total_fines'] }}</td>
                        </tr>
                        <tr>
                            <td>Faculty</td>
                            <td>{{ $analysis['faculty']['count'] }}</td>
                            <td>{{ $analysis['faculty']['percentage'] }}%</td>
                            <td>{{ $analysis['faculty']['avg_days'] }} days</td>
                            <td>₹{{ $analysis['faculty']['total_fines'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Overdue Duration Analysis</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Duration Range</th>
                            <th>Count</th>
                            <th>Percentage</th>
                            <th>Avg. Fine</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1-7 days</td>
                            <td>{{ $analysis['duration']['week1']['count'] }}</td>
                            <td>{{ $analysis['duration']['week1']['percentage'] }}%</td>
                            <td>₹{{ $analysis['duration']['week1']['avg_fine'] }}</td>
                        </tr>
                        <tr>
                            <td>8-14 days</td>
                            <td>{{ $analysis['duration']['week2']['count'] }}</td>
                            <td>{{ $analysis['duration']['week2']['percentage'] }}%</td>
                            <td>₹{{ $analysis['duration']['week2']['avg_fine'] }}</td>
                        </tr>
                        <tr>
                            <td>15-30 days</td>
                            <td>{{ $analysis['duration']['month1']['count'] }}</td>
                            <td>{{ $analysis['duration']['month1']['percentage'] }}%</td>
                            <td>₹{{ $analysis['duration']['month1']['avg_fine'] }}</td>
                        </tr>
                        <tr>
                            <td>30+ days</td>
                            <td>{{ $analysis['duration']['month_plus']['count'] }}</td>
                            <td>{{ $analysis['duration']['month_plus']['percentage'] }}%</td>
                            <td>₹{{ $analysis['duration']['month_plus']['avg_fine'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Repeat Offenders -->
<div class="card shadow mt-4">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">Frequent Overdue Offenders</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Member</th>
                        <th>Type</th>
                        <th>Total Overdue</th>
                        <th>Avg. Overdue Days</th>
                        <th>Total Fines</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($repeat_offenders as $index => $offender)
                    <tr>
                        <td>
                            <span class="badge bg-{{ $index < 3 ? 'danger' : 'warning' }}">
                                #{{ $index + 1 }}
                            </span>
                        </td>
                        <td>{{ $offender->full_name }}</td>
                        <td>{{ $offender->member_type }}</td>
                        <td>{{ $offender->overdue_count }}</td>
                        <td>{{ $offender->avg_overdue_days }} days</td>
                        <td class="text-danger">₹{{ $offender->total_fines }}</td>
                        <td>
                            <a href="{{ route('members.show', $offender->member_id) }}" 
                               class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <button class="btn btn-sm btn-outline-warning" 
                                    onclick="sendWarning({{ $offender->member_id }})">
                                <i class="bi bi-exclamation-triangle"></i> Warn
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Apply Fines Modal -->
<div class="modal fade" id="applyFinesModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('fines.apply-bulk') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Apply Fines to Selected Overdue Borrowings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        This will apply fines to all selected overdue borrowings. Fine rate: ₹{{ $fine_rate }} per day.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Fine Rate per Day</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" name="fine_per_day" class="form-control" 
                                   value="{{ $fine_rate }}" step="0.5">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Selected Borrowings</label>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            <div id="selectedItems"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Fines</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize date pickers
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    if(!$('input[name="start_date"]').val()) {
        $('input[name="start_date"]').val(firstDay.toISOString().split('T')[0]);
    }
    
    if(!$('input[name="end_date"]').val()) {
        $('input[name="end_date"]').val(lastDay.toISOString().split('T')[0]);
    }
    
    // Select all functionality
    $('#selectAllOverdue').change(function() {
        $('.overdue-checkbox').prop('checked', this.checked);
        updateSelectedItems();
    });
    
    $('.overdue-checkbox').change(function() {
        updateSelectedItems();
    });
    
    function updateSelectedItems() {
        const selected = [];
        $('.overdue-checkbox:checked').each(function() {
            const row = $(this).closest('tr');
            const memberName = row.find('td:nth-child(3) strong').text();
            const bookTitle = row.find('td:nth-child(4) strong').text();
            selected.push(`${memberName} - ${bookTitle}`);
        });
        
        $('#selectedItems').empty();
        if (selected.length > 0) {
            selected.forEach(item => {
                $('#selectedItems').append(`<div class="mb-1"><i class="bi bi-check-circle text-success me-2"></i>${item}</div>`);
            });
        } else {
            $('#selectedItems').html('<div class="text-muted">No items selected</div>');
        }
    }
});

function printReport() {
    window.print();
}

function exportReport() {
    // Implement export to CSV/Excel
    alert('Export functionality would be implemented here');
}

function applyFine(transactionId) {
    if(confirm('Apply fine for this overdue borrowing?')) {
        $.ajax({
            url: '{{ route("fines.apply") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                transaction_id: transactionId
            },
            success: function(response) {
                alert('Fine applied successfully');
                location.reload();
            }
        });
    }
}

function sendReminders() {
    const selected = [];
    $('.overdue-checkbox:checked').each(function() {
        selected.push($(this).val());
    });
    
    if(selected.length === 0) {
        alert('Please select overdue borrowings to send reminders');
        return;
    }
    
    if(confirm(`Send reminders for ${selected.length} selected overdue borrowing(s)?`)) {
        $.ajax({
            url: '{{ route("reports.send-reminders") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                transaction_ids: selected
            },
            success: function(response) {
                alert(response.message);
            }
        });
    }
}

function loadTemplate(select, transactionId) {
    const template = select.value;
    const messageTextarea = $(`#message${transactionId}`);
    
    let message = '';
    
    switch(template) {
        case 'gentle_reminder':
            message = `Dear Member,\n\nThis is a gentle reminder that your borrowed book is overdue. Please return it at your earliest convenience.\n\nThank you,\nLibrary Management`;
            break;
        case 'warning':
            message = `Dear Member,\n\nThis is a formal warning that your borrowed book is significantly overdue. Failure to return it may result in additional fines and borrowing restrictions.\n\nPlease return the book immediately.\n\nLibrary Management`;
            break;
        case 'final_notice':
            message = `Dear Member,\n\nFINAL NOTICE: Your borrowed book is seriously overdue. This is your final notice before we take further action, which may include suspension of borrowing privileges and referral to appropriate authorities.\n\nReturn the book IMMEDIATELY.\n\nLibrary Management`;
            break;
        default:
            return;
    }
    
    messageTextarea.val(message);
}

function sendMessage(transactionId) {
    const message = $(`#message${transactionId}`).val();
    
    if(!message.trim()) {
        alert('Please enter a message');
        return;
    }
    
    if(confirm('Send message to member?')) {
        $.ajax({
            url: '{{ route("reports.send-message") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                transaction_id: transactionId,
                message: message
            },
            success: function(response) {
                alert('Message sent successfully');
                $(`#contactModal${transactionId}`).modal('hide');
            }
        });
    }
}

function sendWarning(memberId) {
    if(confirm('Send warning to this member?')) {
        $.ajax({
            url: '{{ route("members.send-warning") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                member_id: memberId
            },
            success: function(response) {
                alert('Warning sent successfully');
            }
        });
    }
}
</script>
@endpush