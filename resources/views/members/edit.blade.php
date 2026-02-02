@extends('layouts.app')

@section('title', 'Edit Member: ' . $member->full_name)

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Edit Member: {{ $member->full_name }}</h5>
                <a href="{{ route('members.show', $member->id) }}" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-eye me-1"></i> View Profile
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('members.update', $member->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Member Type -->
                    <div class="mb-4">
                        <h6 class="mb-3 text-primary">
                            <i class="bi bi-person-badge me-2"></i>Member Type
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check card border p-3 mb-3">
                                    <input class="form-check-input" type="radio" name="member_type" 
                                           id="student" value="STUDENT" {{ $member->member_type == 'STUDENT' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="student">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-circle p-2 me-3">
                                                <i class="bi bi-mortarboard"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Student</h6>
                                                <small class="text-muted">7 days borrowing period</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check card border p-3 mb-3">
                                    <input class="form-check-input" type="radio" name="member_type" 
                                           id="faculty" value="FACULTY" {{ $member->member_type == 'FACULTY' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="faculty">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-info text-white rounded-circle p-2 me-3">
                                                <i class="bi bi-person-workspace"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Faculty</h6>
                                                <small class="text-muted">14 days borrowing period</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Personal Information -->
                    <div class="mb-4">
                        <h6 class="mb-3 text-primary">
                            <i class="bi bi-person-circle me-2"></i>Personal Information
                        </h6>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" name="full_name" class="form-control" required 
                                           value="{{ old('full_name', $member->full_name) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">HRMS User ID *</label>
                                    <input type="number" name="hrms_user_id" class="form-control" required 
                                           value="{{ old('hrms_user_id', $member->hrms_user_id) }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email Address *</label>
                                    <input type="email" name="email" class="form-control" required 
                                           value="{{ old('email', $member->email) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control" 
                                           value="{{ old('phone', $member->phone) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Borrowing Settings -->
                    <div class="mb-4">
                        <h6 class="mb-3 text-primary">
                            <i class="bi bi-book me-2"></i>Borrowing Settings
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Borrow Limit</label>
                                    <div class="input-group">
                                        <input type="number" name="borrow_limit" class="form-control" 
                                               value="{{ old('borrow_limit', $member->borrow_limit) }}" min="1" max="10">
                                        <span class="input-group-text">books</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="ACTIVE" {{ $member->status == 'ACTIVE' ? 'selected' : '' }}>Active</option>
                                        <option value="INACTIVE" {{ $member->status == 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('members.show', $member->id) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Back to Profile
                        </a>
                        <div class="btn-group">
                            <a href="{{ route('members.show', $member->id) }}" class="btn btn-outline-info">
                                <i class="bi bi-eye me-1"></i> View
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Update Member
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Member Statistics -->
        <div class="card shadow mt-4">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">
                    <i class="bi bi-graph-up me-1"></i>Member Statistics
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h4>{{ $member->activeBorrowings->count() }}</h4>
                        <small class="text-muted">Active Borrowings</small>
                    </div>
                    <div class="col-md-3">
                        <h4>{{ $member->totalBorrowings }}</h4>
                        <small class="text-muted">Total Borrowings</small>
                    </div>
                    <div class="col-md-3">
                        <h4>₹{{ $member->totalFinesPaid }}</h4>
                        <small class="text-muted">Total Fines Paid</small>
                    </div>
                    <div class="col-md-3">
                        <h4>{{ $member->daysSinceRegistration }}</h4>
                        <small class="text-muted">Days as Member</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection