@extends('layouts.app')

@section('title', 'Register New Member')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Register New Member</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('members.store') }}" method="POST">
                    @csrf
                    
                    <!-- Member Type Selection -->
                    <div class="mb-4">
                        <h6 class="mb-3 text-primary">
                            <i class="bi bi-person-badge me-2"></i>Member Type
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check card border p-3 mb-3">
                                    <input class="form-check-input" type="radio" name="member_type" 
                                           id="student" value="STUDENT" checked>
                                    <label class="form-check-label" for="student">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-circle p-2 me-3">
                                                <i class="bi bi-mortarboard"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Student</h6>
                                                <small class="text-muted">7 days borrowing period, 3 book limit</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check card border p-3 mb-3">
                                    <input class="form-check-input" type="radio" name="member_type" 
                                           id="faculty" value="FACULTY">
                                    <label class="form-check-label" for="faculty">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-info text-white rounded-circle p-2 me-3">
                                                <i class="bi bi-person-workspace"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Faculty</h6>
                                                <small class="text-muted">14 days borrowing period, 5 book limit</small>
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
                                           placeholder="Enter full name">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">HRMS User ID *</label>
                                    <input type="number" name="hrms_user_id" class="form-control" required 
                                           placeholder="HRMS ID">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email Address *</label>
                                    <input type="email" name="email" class="form-control" required 
                                           placeholder="email@example.com">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control" 
                                           placeholder="+91 98765 43210">
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
                                               value="3" min="1" max="10">
                                        <span class="input-group-text">books</span>
                                    </div>
                                    <small class="text-muted">Maximum number of books that can be borrowed simultaneously</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="ACTIVE" selected>Active</option>
                                        <option value="INACTIVE">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('members.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-person-plus me-1"></i> Register Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Quick Help -->
        <div class="card shadow mt-4">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">
                    <i class="bi bi-info-circle me-1"></i>Registration Guidelines
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        <small>Ensure HRMS User ID is correct and unique</small>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        <small>Email address will be used for notifications</small>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        <small>Faculty members get extended borrowing period</small>
                    </li>
                    <li>
                        <i class="bi bi-check-circle text-success me-2"></i>
                        <small>Borrow limit can be adjusted based on member category</small>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection