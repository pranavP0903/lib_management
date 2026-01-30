@extends('layouts.app')

@section('title', 'Reports & Analytics')
@section('subtitle', 'Library statistics and reports')

@section('content')

<!-- Quick Reports -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <a href="{{ route('reports.circulation') }}" class="card shadow text-decoration-none h-100">
            <div class="card-body text-center">
                <i class="bi bi-arrow-left-right fs-1 text-primary"></i>
                <h6 class="mt-2">Circulation</h6>
                <p class="text-muted small">Issued & Returned</p>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <a href="{{ route('reports.overdue') }}" class="card shadow text-decoration-none h-100">
            <div class="card-body text-center">
                <i class="bi bi-exclamation-triangle fs-1 text-danger"></i>
                <h6 class="mt-2">Overdue</h6>
                <p class="text-muted small">Late Returns</p>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <a href="{{ route('reports.member-activity') }}" class="card shadow text-decoration-none h-100">
            <div class="card-body text-center">
                <i class="bi bi-people fs-1 text-success"></i>
                <h6 class="mt-2">Members</h6>
                <p class="text-muted small">Usage Stats</p>
            </div>
        </a>
    </div>


<!-- Monthly Statistics -->
<div class="card shadow mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Monthly Statistics ({{ date('F Y') }})</h5>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-3">
                <h3 class="text-primary">{{ $monthly_stats['books_issued'] }}</h3>
                <small class="text-muted">Books Issued</small>
            </div>
            <div class="col-md-3">
                <h3 class="text-success">{{ $monthly_stats['books_returned'] }}</h3>
                <small class="text-muted">Books Returned</small>
            </div>
            <div class="col-md-3">
                <h3 class="text-danger">{{ $monthly_stats['overdue_cases'] }}</h3>
                <small class="text-muted">Overdue Cases</small>
            </div>
            <div class="col-md-3">
                <h3 class="text-warning">₹{{ $monthly_stats['fines_collected'] }}</h3>
                <small class="text-muted">Fines Collected</small>
            </div>
        </div>
    </div>
</div>

<!-- Generate Report (UI ONLY – No backend yet) -->
<div class="card shadow mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Generate Custom Report</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info mb-0">
            <i class="bi bi-info-circle me-1"></i>
            Custom report generation will be enabled in the next phase.
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="card shadow">
    <div class="card-header bg-white">
        <h5 class="mb-0">Quick Stats</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <small class="text-muted">Total Books</small>
            <h4>{{ $quick_stats['total_books'] }}</h4>
        </div>
        <div class="mb-3">
            <small class="text-muted">Active Members</small>
            <h4>{{ $quick_stats['active_members'] }}</h4>
        </div>
        <div>
            <small class="text-muted">Monthly Circulation</small>
            <h4>{{ $quick_stats['monthly_circulation'] }}</h4>
        </div>
    </div>
</div>

@endsection
