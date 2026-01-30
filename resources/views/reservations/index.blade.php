@extends('layouts.app')
@section('title', 'Book Reservations')
@section('content')
<div class="card shadow">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Book Reservations</h5>
        <span class="badge bg-primary">{{ $reservations->count() }} pending</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table datatable">
                <thead>
                    <tr>
                        <th>Reservation ID</th>
                        <th>Member</th>
                        <th>Book</th>
                        <th>Request Date</th>
                        <th>Status</th>
                        <th>Position</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reservations as $reservation)
                    <tr>
                        <td>#{{ $reservation->reservation_id }}</td>
                        <td>{{ $reservation->member->full_name }}</td>
                        <td>{{ $reservation->book->title }}</td>
                        <td>{{ $reservation->reservation_date->format('M d, Y') }}</td>
                        <td>
                            <span class="badge bg-{{ $reservation->status == 'WAITING' ? 'warning' : 'success' }}">
                                {{ $reservation->status }}
                            </span>
                        </td>
                        <td>{{ $reservation->position_in_queue }}</td>
                        <td>
                            @if($reservation->status == 'WAITING' && $reservation->book->availableCopies() > 0)
                            <form action="{{ route('reservations.allocate', $reservation->reservation_id) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="bi bi-check-circle"></i> Allocate
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('reservations.destroy', $reservation->reservation_id) }}" 
                                  method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection