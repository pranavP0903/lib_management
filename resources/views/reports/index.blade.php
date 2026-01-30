@extends('layouts.app')

@section('title', 'Reports & Analytics')
@section('subtitle', 'Library statistics and reports')

@section('content')
<div class="row">
    <!-- Quick Reports -->
    <div class="col-lg-3 col-md-6 mb-4">
        <a href="{{ route('reports.circulation') }}" class="card text-decoration-none shadow h-100">
            <div class="card-body text-center">
                <div class="bg-primary text-white rounded-circle p-3 mx-auto mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-arrow-left-right fs-4"></i>
                </div>
                <h5 class="card-title">Circulation Report</h5>
                <p class="text-muted">Books issued and returned</p>
            </div>
        </a>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <a href="{{ route('reports.overdue') }}" class="card text-decoration-none shadow h-100">
            <div class="card-body text-center">
                <div class="bg-danger text-white rounded-circle p-3 mx-auto mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-exclamation-triangle fs-4"></i>
                </div>
                <h5 class="card-title">Overdue Report</h5>
                <p class="text-muted">Late returns and fines</p>
            </div>
        </a>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <a href="{{ route('reports.member-activity') }}" class="card text-decoration-none shadow h-100">
            <div class="card-body text-center">
                <div class="bg-success text-white rounded-circle p-3 mx-auto mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-people fs-4"></i>
                </div>
                <h5 class="card-title">Member Activity</h5>
                <p class="text-muted">Member usage statistics</p>
            </div>
        </a>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <a href="{{ route('reports.inventory') }}" class="card text-decoration-none shadow h-100">
            <div class="card-body text-center">
                <div class="bg-info text-white rounded-circle p-3 mx-auto mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-book fs-4"></i>
                </div>
                <h5 class="card-title">Inventory Report</h5>
                <p class="text-muted">Books and copies status</p>
            </div>
        </a>
    </div>
</div>

<!-- Monthly Statistics -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Monthly Statistics - {{ date('F Y') }}</h5>
                <select id="monthSelector" class="form-select w-auto">
                    <option value="1">January</option>
                    <option value="2">February</option>
                    <option value="3">March</option>
                    <option value="4">April</option>
                    <option value="5">May</option>
                    <option value="6">June</option>
                    <option value="7">July</option>
                    <option value="8">August</option>
                    <option value="9">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12" selected>December</option>
                </select>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h2 class="text-primary">{{ $monthly_stats['books_issued'] }}</h2>
                                <p class="text-muted mb-0">Books Issued</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h2 class="text-success">{{ $monthly_stats['books_returned'] }}</h2>
                                <p class="text-muted mb-0">Books Returned</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h2 class="text-danger">{{ $monthly_stats['overdue_cases'] }}</h2>
                                <p class="text-muted mb-0">Overdue Cases</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h2 class="text-warning">₹{{ $monthly_stats['fines_collected'] }}</h2>
                                <p class="text-muted mb-0">Fines Collected</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Generator -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Generate Custom Report</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('reports.generate') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Report Type</label>
                                <select name="report_type" class="form-select" required>
                                    <option value="">Select Report Type</option>
                                    <option value="circulation">Circulation Report</option>
                                    <option value="overdue">Overdue Report</option>
                                    <option value="member_activity">Member Activity</option>
                                    <option value="inventory">Inventory Report</option>
                                    <option value="fine_collection">Fine Collection</option>
                                    <option value="popular_books">Popular Books</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Format</label>
                                <select name="format" class="form-select" required>
                                    <option value="view">View in Browser</option>
                                    <option value="pdf">PDF Document</option>
                                    <option value="excel">Excel Spreadsheet</option>
                                    <option value="csv">CSV File</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Filters (Optional)</label>
                        <div class="row">
                            <div class="col-md-6">
                                <select name="member_type" class="form-select">
                                    <option value="">All Member Types</option>
                                    <option value="STUDENT">Students Only</option>
                                    <option value="FACULTY">Faculty Only</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <select name="book_category" class="form-select">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category }}">{{ $category }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="reset" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Clear
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-file-earmark-text me-1"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Recent Reports -->
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Recently Generated Reports</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @forelse($recent_reports as $report)
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">{{ $report->report_type }}</h6>
                            <small>{{ $report->created_at->diffForHumans() }}</small>
                        </div>
                        <small class="text-muted">
                            {{ $report->start_date }} to {{ $report->end_date }}
                            • {{ strtoupper($report->format) }}
                        </small>
                    </a>
                    @empty
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-text fs-1 text-muted"></i>
                        <p class="mt-2">No reports generated yet</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="card shadow mt-4">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">Quick Stats</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Total Books in Library</small>
                    <h4 class="mb-0">{{ $quick_stats['total_books'] }}</h4>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Active Members</small>
                    <h4 class="mb-0">{{ $quick_stats['active_members'] }}</h4>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Monthly Circulation</small>
                    <h4 class="mb-0">{{ $quick_stats['monthly_circulation'] }}</h4>
                </div>
                <div class="mb-0">
                    <small class="text-muted">Collection Growth (Last Month)</small>
                    <h4 class="mb-0">+{{ $quick_stats['new_books'] }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pre-defined Reports -->
<div class="card shadow mt-4">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">Pre-defined Reports</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="card border">
                    <div class="card-body">
                        <h6 class="card-title">Daily Circulation Summary</h6>
                        <p class="text-muted small">Books issued and returned today</p>
                        <a href="{{ route('reports.daily-summary') }}" class="btn btn-sm btn-outline-primary">
                            Generate
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border">
                    <div class="card-body">
                        <h6 class="card-title">Member Borrowing History</h6>
                        <p class="text-muted small">Complete borrowing history of members</p>
                        <a href="{{ route('reports.member-history') }}" class="btn btn-sm btn-outline-primary">
                            Generate
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border">
                    <div class="card-body">
                        <h6 class="card-title">Fine Collection Report</h6>
                        <p class="text-muted small">Fines collected and pending</p>
                        <a href="{{ route('reports.fine-collection') }}" class="btn btn-sm btn-outline-primary">
                            Generate
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Set default dates
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    $('input[name="start_date"]').val(firstDay.toISOString().split('T')[0]);
    $('input[name="end_date"]').val(lastDay.toISOString().split('T')[0]);
    
    // Month selector change
    $('#monthSelector').change(function() {
        const month = $(this).val();
        const year = new Date().getFullYear();
        
        // AJAX call to update monthly stats
        $.ajax({
            url: '{{ route("reports.monthly-stats") }}',
            data: { month: month, year: year },
            success: function(response) {
                // Update the monthly stats display
                // This would be implemented with actual data
            }
        });
    });
});
</script>
@endpush