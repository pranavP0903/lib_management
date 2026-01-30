@extends('layouts.app')
@section('title', 'Return Book')
@section('content')
<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Return Book</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('circulation.return.submit') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">Scan or Enter Book Copy ID / ISBN</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                            <input type="text" name="copy_id" class="form-control" 
                                   placeholder="Scan barcode or enter copy ID..." required autofocus>
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i> Find
                            </button>
                        </div>
                        <small class="text-muted">Enter Copy ID, ISBN, or scan barcode</small>
                    </div>
                </form>
                
                <!-- Active Borrowings Table -->
                @if(isset($active_borrowings) && $active_borrowings->count() > 0)
                <div class="mt-4">
                    <h6>Active Borrowings</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Copy ID</th>
                                    <th>Book</th>
                                    <th>Member</th>
                                    <th>Due Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($active_borrowings as $borrowing)
                                <tr class="{{ $borrowing->is_overdue ? 'table-danger' : '' }}">
                                    <td>#{{ $borrowing->copy_id }}</td>
                                    <td>{{ $borrowing->book_title }}</td>
                                    <td>{{ $borrowing->member_name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $borrowing->is_overdue ? 'danger' : 'warning' }}">
                                            {{ $borrowing->due_date }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-success return-btn" 
                                                data-borrowing-id="{{ $borrowing->id }}">
                                            Return
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Return Statistics -->
        <div class="card shadow mt-4">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">Today's Returns</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <h3>{{ $today_returns }}</h3>
                        <small class="text-muted">Today</small>
                    </div>
                    <div class="col-md-4">
                        <h3>{{ $week_returns }}</h3>
                        <small class="text-muted">This Week</small>
                    </div>
                    <div class="col-md-4">
                        <h3>{{ $overdue_returns }}</h3>
                        <small class="text-muted">Overdue</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection