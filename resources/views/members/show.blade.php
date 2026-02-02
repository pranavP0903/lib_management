@extends('layouts.app')

@section('title', $member->full_name)

@section('header-buttons')
<div class="btn-group">
    <a href="{{ route('members.edit', $member->id) }}" class="btn btn-outline-primary">
        <i class="bi bi-pencil me-1"></i> Edit
    </a>

    <a href="{{ route('circulation.issue', ['member_id' => $member->member_id]) }}" class="btn btn-primary">
        <i class="bi bi-book me-1"></i> Issue Book
    </a>
</div>
@endsection

@section('content')
<div class="row">

    <!-- LEFT COLUMN -->
    <div class="col-lg-4">

        <!-- MEMBER PROFILE -->
        <div class="card shadow mb-4">
            <div class="card-body text-center">

                <div class="mb-4">
                    <div class="bg-{{ $member->member_type == 'FACULTY' ? 'info' : 'primary' }} 
                                text-white rounded-circle p-4 mx-auto d-inline-block">
                        <i class="bi bi-{{ $member->member_type == 'FACULTY' ? 'person-workspace' : 'mortarboard' }} fs-1"></i>
                    </div>
                </div>

                <h4 class="mb-2">{{ $member->full_name }}</h4>

                <p class="text-muted mb-3">
                    <span class="badge bg-{{ $member->member_type == 'FACULTY' ? 'info' : 'primary' }}">
                        {{ $member->member_type }}
                    </span>
                    <span class="badge bg-{{ $member->status == 'ACTIVE' ? 'success' : 'danger' }} ms-2">
                        {{ $member->status }}
                    </span>
                </p>

                <div class="mb-4">
                    <p class="mb-2">
                        <i class="bi bi-envelope me-2 text-muted"></i>{{ $member->email }}
                    </p>
                    @if($member->phone)
                        <p class="mb-2">
                            <i class="bi bi-telephone me-2 text-muted"></i>{{ $member->phone }}
                        </p>
                    @endif
                    <p class="mb-0">
                        <i class="bi bi-person-badge me-2 text-muted"></i>
                        HRMS ID: {{ $member->hrms_user_id }}
                    </p>
                </div>

                <!-- ACTIONS -->
                <div class="d-grid gap-2">
                    <a href="{{ route('circulation.issue', ['member_id' => $member->member_id]) }}" 
                       class="btn btn-primary">
                        <i class="bi bi-book-plus me-1"></i> Issue Book
                    </a>

                    @if($member->status == 'ACTIVE')
                        <form action="{{ route('members.deactivate', $member->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-outline-warning w-100">
                                <i class="bi bi-person-x me-1"></i> Deactivate
                            </button>
                        </form>
                    @else
                        <form action="{{ route('members.activate', $member->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-outline-success w-100">
                                <i class="bi bi-person-check me-1"></i> Activate
                            </button>
                        </form>
                    @endif
                </div>

            </div>

            <div class="card-footer bg-white">
                <small class="text-muted">
                    <i class="bi bi-calendar me-1"></i>
                    Member since {{ $member->created_at->format('M d, Y') }}
                </small>
            </div>
        </div>

        <!-- BORROW STATS -->
        <div class="card shadow">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">Borrowing Statistics</h6>
            </div>
            <div class="card-body">

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Current Borrowings:</span>
                        <span class="badge bg-warning">
                            {{ $member->activeBorrowings->count() }}/{{ $member->borrow_limit }}
                        </span>
                    </div>
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar bg-warning"
                             style="width: {{ ($member->activeBorrowings->count() / max(1,$member->borrow_limit)) * 100 }}%">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <span>Pending Fines:</span>
                    <span class="badge bg-danger">₹{{ $member->pendingFines }}</span>
                </div>

                <div class="mb-3">
                    <span>Total Borrowings:</span>
                    <span class="badge bg-info">{{ $member->totalBorrowings }}</span>
                </div>

                <div>
                    <span>Overdue Books:</span>
                    <span class="badge bg-danger">{{ $member->overdueBorrowings }}</span>
                </div>

            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN (BORROWINGS & HISTORY) -->
    <div class="col-lg-8">
        {{-- unchanged UI --}}
    </div>

</div>
@endsection
