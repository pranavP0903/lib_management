@extends('layouts.app')
@section('title', 'Fines Management')
@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Pending Fines</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>Fine ID</th>
                                <th>Member</th>
                                <th>Book</th>
                                <th>Overdue Days</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fines as $fine)
                            <tr>
                                <td>#{{ $fine->fine_id }}</td>
                                <td>{{ $fine->circulation->member->full_name }}</td>
                                <td>{{ $fine->circulation->copy->book->title }}</td>
                                <td>{{ $fine->overdue_days }} days</td>
                                <td class="fw-bold">₹{{ $fine->fine_amount }}</td>
                                <td>
                                    <span class="badge bg-{{ $fine->fine_status == 'PENDING' ? 'warning' : 'success' }}">
                                        {{ $fine->fine_status }}
                                    </span>
                                </td>
                                <td>
                                    @if($fine->fine_status == 'PENDING')
                                    <form action="{{ route('fines.pay', $fine->fine_id) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-cash"></i> Mark Paid
                                        </button>
                                    </form>
                                    <form action="{{ route('fines.waive', $fine->fine_id) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning">
                                            <i class="bi bi-x-circle"></i> Waive
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Fine Summary -->
        <div class="card shadow">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">Fine Summary</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Total Pending</small>
                    <h3>₹{{ $total_pending }}</h3>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Total Collected</small>
                    <h3>₹{{ $total_collected }}</h3>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Total Waived</small>
                    <h3>₹{{ $total_waived }}</h3>
                </div>
                <hr>
                <div>
                    <small class="text-muted">Fine Rate</small>
                    <h5>₹{{ $fine_rate }} per day</h5>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection