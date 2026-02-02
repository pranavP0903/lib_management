@extends('layouts.app')

@section('title', $book->title)

@section('header-buttons')
    <div class="btn-group">
        <a href="{{ route('books.edit', $book->id) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        @if(!$book->isReleased())
            <button class="btn btn-secondary" disabled>
                <i class="bi bi-clock-history me-1"></i> Will be available on {{ $book->release_at?->format('M d, Y H:i') }}
            </button>
        @else
            <a href="{{ route('circulation.issue') }}?book_id={{ $book->id }}" class="btn btn-primary">
                <i class="bi bi-arrow-up-right me-1"></i> Issue This Book
            </a>
        @endif
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4">
        <!-- Book Card -->
        <div class="card shadow mb-4">
            <div class="card-body text-center">
                <!-- Book Cover/Icon -->
                <div class="mb-4">
                    @if($book->image_path && file_exists(public_path('storage/' . $book->image_path)))
                        <div class="rounded mx-auto" style="width: 200px; height: 250px; overflow: hidden;">
                            <img src="{{ asset('storage/' . $book->image_path) }}" alt="{{ $book->title }}" 
                                 style="width:100%;height:100%;object-fit:cover;border-radius:0.375rem;">
                        </div>
                    @else
                        <div class="bg-light rounded p-5 mx-auto" style="width: 200px; height: 250px;">
                            @if($book->digital_resource_url)
                                <i class="bi bi-file-earmark-pdf text-primary fs-1"></i>
                                <div class="mt-3">
                                    <span class="badge bg-info fs-6">Digital Available</span>
                                </div>
                            @else
                                <i class="bi bi-book text-primary fs-1"></i>
                                <div class="mt-3">
                                    <span class="badge bg-secondary fs-6">Physical Only</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
                
                <!-- Quick Info -->
                <div class="mb-4">
                    <h4 class="mb-3">{{ $book->title }}</h4>
                    <p class="text-muted mb-2">
                        <i class="bi bi-person me-1"></i>{{ $book->author }}
                    </p>
                    <p class="text-muted mb-3">
                        <i class="bi bi-tag me-1"></i>{{ $book->category }}
                    </p>
                    
                    <div class="d-flex justify-content-center gap-3 mb-3">
                        <div class="text-center">
                            <h5 class="mb-0">{{ $book->copies->count() }}</h5>
                            <small class="text-muted">Total Copies</small>
                        </div>
                        <div class="text-center">
                            <h5 class="mb-0">{{ $book->availableCopies() }}</h5>
                            <small class="text-muted">Available</small>
                        </div>
                        <div class="text-center">
                            <h5 class="mb-0">{{ $book->copies->where('status', 'ISSUED')->count() }}</h5>
                            <small class="text-muted">Issued</small>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="d-grid gap-2">
                    @if(!$book->isReleased())
                        <button class="btn btn-secondary" disabled>
                            <i class="bi bi-clock-history me-1"></i> Will be available on {{ $book->release_at?->format('M d, Y H:i') }}
                        </button>
                    @else
                        <a href="{{ route('circulation.issue') }}?book_id={{ $book->id }}" class="btn btn-primary">
                            <i class="bi bi-arrow-up-right me-1"></i> Issue This Book
                        </a>
                    @endif
                    @if($book->availableCopies() == 0)
                    <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#reserveModal">
                        <i class="bi bi-clock-history me-1"></i> Reserve
                    </button>
                    @endif
                    @if($book->digital_resource_url)
                    <a href="{{ $book->digital_resource_url }}" target="_blank" class="btn btn-outline-info">
                        <i class="bi bi-download me-1"></i> Access Digital Version
                    </a>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Book Information -->
        <div class="card shadow">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">Book Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th width="40%">ISBN:</th>
                        <td><code>{{ $book->isbn }}</code></td>
                    </tr>
                    <tr>
                        <th>Publisher:</th>
                        <td>{{ $book->publisher ?? 'Not specified' }}</td>
                    </tr>
                    <tr>
                        <th>Edition:</th>
                        <td>{{ $book->edition ?? 'Not specified' }}</td>
                    </tr>
                    <tr>
                        <th>Added On:</th>
                        <td>{{ $book->created_at->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <th>Last Updated:</th>
                        <td>{{ $book->updated_at->format('M d, Y') }}</td>
                    </tr>
                    @if($book->digital_resource_url)
                    <tr>
                        <th>Digital URL:</th>
                        <td>
                            <a href="{{ $book->digital_resource_url }}" target="_blank" class="text-truncate d-inline-block" style="max-width: 200px;">
                                {{ $book->digital_resource_url }}
                            </a>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <!-- Copies Management -->
        <div class="card shadow mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Book Copies</h5>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addCopyModal">
                    <i class="bi bi-plus-circle me-1"></i> Add Copy
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Copy ID</th>
                                <th>Copy Number</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Current Holder</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($book->copies as $copy)
                            <tr>
                                <td><span class="badge bg-secondary">#{{ $copy->id }}</span></td>
                                <td><strong>{{ $copy->copy_number }}</strong></td>
                                <td>{{ $copy->location ?? 'Not specified' }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'AVAILABLE' => 'success',
                                            'ISSUED' => 'warning',
                                            'RESERVED' => 'info',
                                            'LOST' => 'danger'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$copy->status] ?? 'secondary' }}">
                                        {{ $copy->status }}
                                    </span>
                                </td>
                                <td>
                                    @if($copy->status == 'ISSUED' && $copy->currentCirculation)
                                        {{ $copy->currentCirculation->member->full_name }}
                                        <br>
                                        <small class="text-muted">Due: {{ $copy->currentCirculation->due_date->format('M d') }}</small>
                                    @elseif($copy->status == 'RESERVED')
                                        <small class="text-muted">Reserved</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        @if($copy->status == 'AVAILABLE')
                                            @if($book->isReleased())
                                                <a href="{{ route('circulation.issue') }}?copy_id={{ $copy->id }}" 
                                                   class="btn btn-outline-success" title="Issue">
                                                    <i class="bi bi-arrow-up-right"></i>
                                                </a>
                                            @else
                                                <button class="btn btn-outline-secondary" disabled title="Not yet released">
                                                    <i class="bi bi-clock-history"></i>
                                                </button>
                                            @endif
                                        @elseif($copy->status == 'ISSUED' && $copy->currentBorrowing)
                                        <form action="{{ route('circulation.return.submit') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="circulation_id" value="{{ $copy->currentCirculation->id }}">
                                            <button type="submit" class="btn btn-outline-primary" title="Return">
                                                <i class="bi bi-arrow-down-left"></i>
                                            </button>
                                        </form>
                                        @endif
                                        
                                        <button type="button" class="btn btn-outline-secondary" 
                                                data-bs-toggle="modal" data-bs-target="#editCopyModal{{ $copy->id }}"
                                                title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        
                                        @if($copy->status == 'AVAILABLE')
                                        <form action="{{ route('books.copies.destroy', $copy->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" 
                                                    onclick="return confirm('Delete this copy?')" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-book fs-1 text-muted"></i>
                                    <p class="mt-2">No copies added yet</p>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCopyModal">
                                        Add First Copy
                                    </button>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Circulation History -->
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Circulation History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Member</th>
                                <th>Copy</th>
                                <th>Action</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($circulationHistory as $record)
                            <tr>
                                <td>{{ $record->issue_date->format('M d, Y') }}</td>
                                <td>{{ $record->member->full_name }}</td>
                                <td>Copy #{{ $record->copy->copy_number }}</td>
                                <td>{{ $record->return_date ? 'Returned' : 'Issued' }}</td>
                                <td>
                                    @if($record->return_date)
                                        <span class="text-muted">{{ $record->due_date->format('M d, Y') }}</span>
                                    @else
                                        <span class="{{ $record->due_date->isPast() ? 'text-danger' : 'text-success' }}">
                                            {{ $record->due_date->format('M d, Y') }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $record->status == 'RETURNED' ? 'success' : ($record->status == 'OVERDUE' ? 'danger' : 'warning') }}">
                                        {{ $record->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-clock-history fs-1 text-muted"></i>
                                    <p class="mt-2">No circulation history yet</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reserve Modal -->
<div class="modal fade" id="reserveModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('reservations.store') }}" method="POST">
                @csrf
                <input type="hidden" name="book_id" value="{{ $book->id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Reserve "{{ $book->title }}"</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>This book is currently unavailable. You can reserve it and get notified when it becomes available.</p>
                    <div class="mb-3">
                        <label class="form-label">Select Member</label>
                        <select name="member_id" class="form-select" required>
                            <option value="">Select Member</option>
                            @foreach($members as $member)
                            <option value="{{ $member->id }}">{{ $member->full_name }} ({{ $member->member_type }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Place Reservation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Copy Modal -->
<div class="modal fade" id="addCopyModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('books.copies.store', $book->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Copy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Copy Number *</label>
                        <input type="text" name="copy_number" class="form-control" required 
                               placeholder="e.g., C-001, COPY-01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" 
                               placeholder="e.g., Shelf A-12, Rack 3">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Copy</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection