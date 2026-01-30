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
                <div class="search-box">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by name, email, phone or ID..." 
                           value="{{ request('search') }}">
                    <i class="bi bi-search"></i>
                </div>
            </div>
            <div class="col-md-3">
                <select name="member_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="STUDENT" {{ request('member_type') == 'STUDENT' ? 'selected' : '' }}>
                        Students
                    </option>
                    <option value="FACULTY" {{ request('member_type') == 'FACULTY' ? 'selected' : '' }}>
                        Faculty
                    </option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="ACTIVE" {{ request('status') == 'ACTIVE' ? 'selected' : '' }}>
                        Active
                    </option>
                    <option value="INACTIVE" {{ request('status') == 'INACTIVE' ? 'selected' : '' }}>
                        Inactive
                    </option>
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
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
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
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-2 me-3">
                                    <i class="bi bi-person text-primary"></i>
                                </div>
                                <div>
                                    <strong>{{ $member->full_name }}</strong><br>
                                    <small class="text-muted">
                                        HRMS ID: {{ $member->hrms_user_id }}
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <small>
                                    <i class="bi bi-envelope me-1"></i>{{ $member->email }}
                                </small>
                                <br>
                                @if($member->phone)
                                <small>
                                    <i class="bi bi-telephone me-1"></i>{{ $member->phone }}
                                </small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $member->member_type == 'FACULTY' ? 'info' : 'primary' }}">
                                {{ $member->member_type }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $member->status == 'ACTIVE' ? 'success' : 'danger' }}">
                                {{ $member->status }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <small class="text-muted">Borrowings:</small><br>
                                    <span class="badge bg-{{ $member->activeBorrowings->count() >= $member->borrow_limit ? 'danger' : 'warning' }}">
                                        {{ $member->activeBorrowings->count() }}/{{ $member->borrow_limit }}
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted">Fines:</small><br>
                                    <span class="badge bg-{{ $member->pendingFines > 0 ? 'danger' : 'success' }}">
                                        ₹{{ $member->pendingFines }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('members.show', $member->member_id) }}" 
                                   class="btn btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('members.edit', $member->member_id) }}" 
                                   class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($member->status == 'ACTIVE')
                                <form action="{{ route('members.deactivate', $member->member_id) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-warning" title="Deactivate">
                                        <i class="bi bi-person-x"></i>
                                    </button>
                                </form>
                                @else
                                <form action="{{ route('members.activate', $member->member_id) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-success" title="Activate">
                                        <i class="bi bi-person-check"></i>
                                    </button>
                                </form>
                                @endif
                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="confirmDelete('delete-member-{{ $member->member_id }}')"
                                        title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <form id="delete-member-{{ $member->member_id }}" 
                                      action="{{ route('members.destroy', $member->member_id) }}" 
                                      method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($members->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $members->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Stats Cards -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50">Total Members</h6>
                        <h2 class="mb-0">{{ $stats['total_members'] }}</h2>
                    </div>
                    <i class="bi bi-people fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50">Active Members</h6>
                        <h2 class="mb-0">{{ $stats['active_members'] }}</h2>
                    </div>
                    <i class="bi bi-person-check fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50">Faculty</h6>
                        <h2 class="mb-0">{{ $stats['faculty_count'] }}</h2>
                    </div>
                    <i class="bi bi-person-badge fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50">Students</h6>
                        <h2 class="mb-0">{{ $stats['student_count'] }}</h2>
                    </div>
                    <i class="bi bi-person fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection