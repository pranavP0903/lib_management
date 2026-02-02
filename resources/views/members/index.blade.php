@extends('layouts.app')

@section('title', 'Library Members')
@section('subtitle', 'Manage student and faculty members')

@section('header-buttons')
    <a href="{{ route('members.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i> Add Member
    </a>
@endsection

@section('content')

<!-- Search and Filter -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="{{ route('members.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control"
                       placeholder="Search by name, email, phone or ID..."
                       value="{{ request('search') }}">
            </div>

            <div class="col-md-3">
                <select name="member_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="STUDENT" {{ request('member_type') === 'STUDENT' ? 'selected' : '' }}>Students</option>
                    <option value="FACULTY" {{ request('member_type') === 'FACULTY' ? 'selected' : '' }}>Faculty</option>
                </select>
            </div>

            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                    <option value="INACTIVE" {{ request('status') === 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Members Table -->
<div class="card shadow">
    <div class="card-body table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Member</th>
                    <th>Contact</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Borrowing</th>
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
                        <strong>{{ $member->full_name }}</strong><br>
                        <small class="text-muted">HRMS ID: {{ $member->hrms_user_id }}</small>
                    </td>

                    <td>
                        <small><i class="bi bi-envelope me-1"></i>{{ $member->email }}</small><br>
                        @if($member->phone)
                            <small><i class="bi bi-telephone me-1"></i>{{ $member->phone }}</small>
                        @endif
                    </td>

                    <td>
                        <span class="badge bg-{{ $member->member_type === 'FACULTY' ? 'info' : 'primary' }}">
                            {{ $member->member_type }}
                        </span>
                    </td>

                    <td>
                        <span class="badge bg-{{ $member->status === 'ACTIVE' ? 'success' : 'danger' }}">
                            {{ $member->status }}
                        </span>
                    </td>

                    <td>
                        <span class="badge bg-warning">
                            {{ $member->activeBorrowings->count() ?? 0 }}/{{ $member->borrow_limit }}
                        </span><br>
                        <span class="badge bg-{{ $member->pendingFines > 0 ? 'danger' : 'success' }}">
                            ₹{{ $member->pendingFines }}
                        </span>
                    </td>

                    <td>
                        <div class="btn-group btn-group-sm">

                            <!-- View -->
                            <a href="{{ route('members.show', $member->id) }}"
                               class="btn btn-outline-info" title="View">
                                <i class="bi bi-eye"></i>
                            </a>

                            <!-- Edit -->
                            <a href="{{ route('members.edit', $member->id) }}"
                               class="btn btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <!-- Activate / Deactivate -->
                            @if($member->status === 'ACTIVE')
                                <form action="{{ route('members.deactivate', $member->id) }}"
                                      method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-outline-warning" title="Deactivate">
                                        <i class="bi bi-person-x"></i>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('members.activate', $member->id) }}"
                                      method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-outline-success" title="Activate">
                                        <i class="bi bi-person-check"></i>
                                    </button>
                                </form>
                            @endif

                            <!-- Delete -->
                            <form action="{{ route('members.destroy', $member->id) }}"
                                  method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>

                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $members->links() }}
    </div>
</div>

@endsection
