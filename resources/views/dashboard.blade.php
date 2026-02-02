@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-primary border-0 shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title text-white-50">Total Books</h5>
                        <h2 class="mb-0 text-white">{{ $stats['total_books'] }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-book fs-1 text-white-50"></i>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-white-50">
                    <span class="me-2">{{ $stats['available_copies'] }} available</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-success border-0 shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title text-white-50">Total Members</h5>
                        <h2 class="mb-0 text-white">{{ $stats['total_members'] }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-people fs-1 text-white-50"></i>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-white-50">
                    <span class="me-2">{{ $stats['active_members'] }} active</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-warning border-0 shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title text-white-50">Active borrowings</h5>
                        <h2 class="mb-0 text-white">{{ $stats['active_borrowings'] }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-arrow-up-right-circle fs-1 text-white-50"></i>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-white-50">
                    <span class="me-2">{{ $stats['todays_returns'] }} due today</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-danger border-0 shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title text-white-50">Pending Fines</h5>
                        <h2 class="mb-0 text-white">₹{{ $stats['pending_fines'] }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-cash-coin fs-1 text-white-50"></i>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-white-50">
                    <span class="me-2">{{ $stats['overdue_books'] }} overdue</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="{{ route('circulation.issue') }}" class="btn btn-primary w-100 py-3">
                            <i class="bi bi-plus-circle fs-4 d-block mb-2"></i>
                            Issue Book
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('circulation.return') }}" class="btn btn-success w-100 py-3">
                            <i class="bi bi-arrow-return-left fs-4 d-block mb-2"></i>
                            Return Book
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('books.create') }}" class="btn btn-info w-100 py-3 text-white">
                            <i class="bi bi-plus-lg fs-4 d-block mb-2"></i>
                            Add New Book
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('members.create') }}" class="btn btn-secondary w-100 py-3">
                            <i class="bi bi-person-plus fs-4 d-block mb-2"></i>
                            Register Member
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities & Overdue Books -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Transactions</h5>
                <a href="{{ route('circulation.active') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Book</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_transactions as $transaction)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded-circle p-2 me-2">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div>
                                            <strong>{{ $transaction->member->full_name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $transaction->member->member_type }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded p-1 me-2">
                                            <i class="bi bi-book text-primary"></i>
                                        </div>
                                        <div>
                                            <strong>{{ \Illuminate\Support\Str::limit($transaction->copy->book->title, 30) }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $transaction->copy->book->author }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $transaction->issue_date->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $transaction->due_date->isPast() ? 'danger' : 'success' }}">
                                        {{ $transaction->due_date->format('M d, Y') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $transaction->status === 'ISSUED' ? 'warning' : ($transaction->status === 'RETURNED' ? 'success' : 'danger') }}">
                                        {{ $transaction->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                    <p class="mt-2">No recent transactions</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Overdue Alerts</h5>
                <span class="badge bg-danger">{{ $overdue_books->count() }}</span>
            </div>
            <div class="card-body">
                @if($overdue_books->count() > 0)
                    <div class="list-group">
                        @foreach($overdue_books as $overdue)
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ \Illuminate\Support\Str::limit($overdue->copy->book->title, 25) }}</h6>
                                <small class="text-danger">{{ $overdue->due_date->diffForHumans() }}</small>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-person me-1"></i>{{ $overdue->member->full_name }}
                                • {{ $overdue->member->email }}
                            </small>
                        </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-check-circle-fill fs-1 text-success"></i>
                        <p class="mt-2">No overdue books</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Popular Books -->
        <div class="card shadow mt-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Most Popular Books</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @foreach($popular_books as $book)
                    <div class="list-group-item border-0">
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded p-2 me-3">
                                <i class="bi bi-book text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ \Illuminate\Support\Str::limit($book->title, 30) }}</h6>
                                <small class="text-muted">{{ $book->author }}</small>
                            </div>
                            <span class="badge bg-info">{{ $book->borrow_count }} borrowings</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activity Chart (Placeholder) -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Monthly Circulation Statistics</h5>
            </div>
            <div class="card-body">
                <div class="text-center py-5">
                    <i class="bi bi-bar-chart fs-1 text-muted"></i>
                    <p class="mt-2 text-muted">Circulation chart will appear here</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection