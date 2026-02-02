@extends('layouts.app')

@section('title', 'Overdue Books')
@section('subtitle', 'Books not returned by due date')

@section('header-buttons')
    <div class="btn-group">
        <button class="btn btn-outline-danger" onclick="sendOverdueAlerts()">
            <i class="bi bi-bell me-1"></i> Send Alerts
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#applyFinesModal">
            <i class="bi bi-cash-coin me-1"></i> Apply Fines
        </button>
    </div>
@endsection

@section('content')
<!-- Overdue Summary -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-white-50">Total Overdue</h6>
                        <h2 class="mb-0">{{ $overdues->count() }}</h2>
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
                        <h2 class="mb-0">₹{{ $total_fines }}</h2>
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
                        <h6 class="card-title text-white-50">Max Overdue Days</h6>
                        <h2 class="mb-0">{{ $max_overdue_days }}</h2>
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
                        <h2 class="mb-0">{{ $affected_members }}</h2>
                    </div>
                    <i class="bi bi-people fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Overdue Books Table -->
<div class="card shadow">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Overdue Books</h5>
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-primary" onclick="printOverdueReport()">
                <i class="bi bi-printer me-1"></i> Print Report
            </button>
            <button class="btn btn-sm btn-outline-success" onclick="exportOverdueCSV()">
                <i class="bi bi-file-earmark-arrow-down me-1"></i> Export
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th>Borrowing ID</th>
                        <th>Member</th>
                        <th>Book</th>
                        <th>Due Date</th>
                        <th>Overdue Days</th>
                        <th>Fine</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($overdues as $borrowing)
                    <tr>
                        <td>
                            <input type="checkbox" name="borrowing_select" value="{{ $borrowing->transaction_id }}">
                        </td>
                        <td>
                            <span class="badge bg-secondary">#{{ $borrowing->transaction_id }}</span>
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
                            <span class="badge bg-danger">{{ $borrowing->due_date->format('M d, Y') }}</span>
                            <br>
                            <small class="text-muted">{{ $borrowing->due_date->diffForHumans() }}</small>
                        </td>
                        <td>
                            <span class="badge bg-{{ $borrowing->overdue_days > 30 ? 'danger' : ($borrowing->overdue_days > 15 ? 'warning' : 'info') }}">
                                {{ $borrowing->overdue_days }} days
                            </span>
                        </td>
                        <td>
                            <strong class="text-danger">₹{{ $borrowing->calculated_fine }}</strong>
                            @if($borrowing->fine_applied)
                            <br>
                            <small class="text-success">Fine applied</small>
                            @endif
                        </td>
                        <td>
                            @if($borrowing->fine_applied)
                                <span class="badge bg-warning">Fine Pending</span>
                            @else
                                <span class="badge bg-danger">No Fine</span>
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
                                @if(!$borrowing->fine_applied)
                                <button type="button" class="btn btn-outline-warning" 
                                        onclick="applyFine({{ $borrowing->transaction_id }})"
                                        title="Apply Fine">
                                    <i class="bi bi-cash-coin"></i>
                                </button>
                                @endif
                                <button type="button" class="btn btn-outline-info" 
                                        data-bs-toggle="modal" data-bs-target="#contactModal{{ $borrowing->transaction_id }}"
                                        title="Contact">
                                    <i class="bi bi-telephone"></i>
                                </button>
                            </div>
                            
                            <!-- Contact Modal -->
                            <div class="modal fade" id="contactModal{{ $borrowing->transaction_id }}">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Contact Member</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <strong>Member:</strong> {{ $borrowing->member->full_name }}
                                            </div>
                                            <div class="mb-3">
                                                <strong>Contact Information:</strong>
                                                <p class="mb-1">Email: {{ $borrowing->member->email }}</p>
                                                @if($borrowing->member->phone)
                                                <p class="mb-0">Phone: {{ $borrowing->member->phone }}</p>
                                                @endif
                                            </div>
                                            <div class="mb-3">
                                                <strong>Overdue Book:</strong>
                                                <p class="mb-1">{{ $borrowing->copy->book->title }}</p>
                                                <p class="mb-0">Overdue: {{ $borrowing->overdue_days }} days</p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Message Template</label>
                                                <select class="form-select" id="messageTemplate">
                                                    <option value="">Select Template</option>
                                                    <option value="reminder">First Reminder</option>
                                                    <option value="warning">Warning Notice</option>
                                                    <option value="final">Final Notice</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Custom Message</label>
                                                <textarea class="form-control" rows="4" id="customMessage">
Dear {{ $borrowing->member->full_name }},

Your book "{{ $borrowing->copy->book->title }}" is overdue by {{ $borrowing->overdue_days }} days.
Please return it immediately to avoid additional fines.

Fine Amount: ₹{{ $borrowing->calculated_fine }}

Thank you,
Library Management
                                                </textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="button" class="btn btn-primary" 
                                                    onclick="sendMessage({{ $borrowing->transaction_id }})">
                                                Send Message
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

<!-- Apply Fines Modal -->
<div class="modal fade" id="applyFinesModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('fines.apply-bulk') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Apply Fines to Overdue Books</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        This will apply fines to all selected overdue borrowings. Fine rate: ₹{{ $fine_rate }} per day.
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAllFines"></th>
                                    <th>Member</th>
                                    <th>Book</th>
                                    <th>Overdue Days</th>
                                    <th>Calculated Fine</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($overdues as $borrowing)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="borrowing_ids[]" value="{{ $borrowing->transaction_id }}" checked>
                                    </td>
                                    <td>{{ $borrowing->member->full_name }}</td>
                                    <td>{{ $borrowing->copy->book->title }}</td>
                                    <td>{{ $borrowing->overdue_days }}</td>
                                    <td>₹{{ $borrowing->calculated_fine }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Fine Rate per Day</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" name="fine_per_day" class="form-control" value="{{ $fine_rate }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Maximum Fine</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" name="max_fine" class="form-control" value="500">
                                </div>
                            </div>
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

<!-- Overdue Analysis -->
<div class="card shadow mt-4">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">Overdue Analysis</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Overdue by Member Type</h6>
                <table class="table table-sm">
                    <tr>
                        <th>Member Type</th>
                        <th>Count</th>
                        <th>Percentage</th>
                    </tr>
                    <tr>
                        <td>Students</td>
                        <td>{{ $stats['students'] }}</td>
                        <td>{{ $stats['students_percentage'] }}%</td>
                    </tr>
                    <tr>
                        <td>Faculty</td>
                        <td>{{ $stats['faculty'] }}</td>
                        <td>{{ $stats['faculty_percentage'] }}%</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Overdue Duration Distribution</h6>
                <table class="table table-sm">
                    <tr>
                        <th>Duration</th>
                        <th>Count</th>
                        <th>Percentage</th>
                    </tr>
                    <tr>
                        <td>1-7 days</td>
                        <td>{{ $stats['week1'] }}</td>
                        <td>{{ $stats['week1_percentage'] }}%</td>
                    </tr>
                    <tr>
                        <td>8-14 days</td>
                        <td>{{ $stats['week2'] }}</td>
                        <td>{{ $stats['week2_percentage'] }}%</td>
                    </tr>
                    <tr>
                        <td>15-30 days</td>
                        <td>{{ $stats['month1'] }}</td>
                        <td>{{ $stats['month1_percentage'] }}%</td>
                    </tr>
                    <tr>
                        <td>30+ days</td>
                        <td>{{ $stats['month_plus'] }}</td>
                        <td>{{ $stats['month_plus_percentage'] }}%</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Select All checkboxes
$('#selectAll').click(function() {
    $('input[name="borrowing_select"]').prop('checked', this.checked);
});

$('#selectAllFines').click(function() {
    $('input[name="borrowing_ids[]"]').prop('checked', this.checked);
});

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
                location.reload();
            }
        });
    }
}

function sendOverdueAlerts() {
    if(confirm('Send overdue alerts to all affected members?')) {
        $.ajax({
            url: '{{ route("circulation.send-overdue-alerts") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                alert(response.message);
            }
        });
    }
}

function sendMessage(transactionId) {
    const message = $('#customMessage').val();
    if(confirm('Send message to member?')) {
        $.ajax({
            url: '{{ route("circulation.send-message") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                transaction_id: transactionId,
                message: message
            },
            success: function(response) {
                alert('Message sent successfully');
                $('#contactModal' + transactionId).modal('hide');
            }
        });
    }
}

function printOverdueReport() {
    window.print();
}

function exportOverdueCSV() {
    alert('CSV export functionality would be implemented here');
}
</script>
@endpush