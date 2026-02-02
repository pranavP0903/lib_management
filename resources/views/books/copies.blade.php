@extends('layouts.app')

@section('title', 'Manage Book Copies')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Book Copies Management</h5>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addCopyModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Copy
                    </button>
                    <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#bulkUpdateModal">
                        <i class="bi bi-arrow-repeat me-1"></i> Bulk Update
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <select id="statusFilter" class="form-select">
                            <option value="">All Status</option>
                            <option value="AVAILABLE">Available</option>
                            <option value="ISSUED">Issued</option>
                            <option value="RESERVED">Reserved</option>
                            <option value="LOST">Lost</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="bookFilter" class="form-select">
                            <option value="">All Books</option>
                            @foreach($books as $book)
                            <option value="{{ $book->id }}">{{ $book->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" id="searchFilter" class="form-control" placeholder="Search copy number...">
                    </div>
                    <div class="col-md-3">
                        <button id="resetFilters" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-arrow-clockwise me-1"></i> Reset
                        </button>
                    </div>
                </div>
                
                <!-- Copies Table -->
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>Copy ID</th>
                                <th>Book</th>
                                <th>Copy Number</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Current Holder</th>
                                <th>Last Activity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($copies as $copy)
                            <tr data-status="{{ $copy->status }}" data-book-id="{{ $copy->book_id }}">
                                <td>
                                    <span class="badge bg-secondary">#{{ $copy->id }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded p-1 me-2">
                                            <i class="bi bi-book text-primary"></i>
                                        </div>
                                        <div>
                                            <strong>{{ $copy->book->title }}</strong><br>
                                            <small class="text-muted">{{ $copy->book->author }}</small>
                                        </div>
                                    </div>
                                </td>
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
                                        <small class="text-muted">Due: {{ $copy->currentCirculation->due_date->format('M d, Y') }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($copy->updated_at)
                                        {{ $copy->updated_at->diffForHumans() }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        @if($copy->status == 'AVAILABLE')
                                        <a href="{{ route('circulation.issue') }}?copy_id={{ $copy->id }}" 
                                           class="btn btn-outline-success" title="Issue">
                                            <i class="bi bi-arrow-up-right"></i>
                                        </a>
                                        @elseif($copy->status == 'ISSUED' && $copy->currentCirculation)
                                        <form action="{{ route('circulation.return.submit') }}" method="POST" class="d-inline">
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
                                        <form action="{{ route('books.copies.destroy', $copy->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" 
                                                    onclick="return confirm('Delete this copy?')" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                    
                                    <!-- Edit Copy Modal -->
                                    <div class="modal fade" id="editCopyModal{{ $copy->id }}">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('books.copies.update', $copy->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Copy #{{ $copy->copy_number }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Copy Number *</label>
                                                            <input type="text" name="copy_number" class="form-control" 
                                                                   value="{{ $copy->copy_number }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Location</label>
                                                            <input type="text" name="location" class="form-control" 
                                                                   value="{{ $copy->location }}">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Status</label>
                                                            <select name="status" class="form-select">
                                                                <option value="AVAILABLE" {{ $copy->status == 'AVAILABLE' ? 'selected' : '' }}>Available</option>
                                                                <option value="ISSUED" {{ $copy->status == 'ISSUED' ? 'selected' : '' }}>Issued</option>
                                                                <option value="RESERVED" {{ $copy->status == 'RESERVED' ? 'selected' : '' }}>Reserved</option>
                                                                <option value="LOST" {{ $copy->status == 'LOST' ? 'selected' : '' }}>Lost</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-white-50">Available</h6>
                        <h2 class="mb-0">{{ $stats['available'] }}</h2>
                    </div>
                    <i class="bi bi-check-circle fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-white-50">Issued</h6>
                        <h2 class="mb-0">{{ $stats['issued'] }}</h2>
                    </div>
                    <i class="bi bi-arrow-up-right-circle fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-white-50">Reserved</h6>
                        <h2 class="mb-0">{{ $stats['reserved'] }}</h2>
                    </div>
                    <i class="bi bi-clock-history fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-white-50">Lost</h6>
                        <h2 class="mb-0">{{ $stats['lost'] }}</h2>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Copy Modal -->
<div class="modal fade" id="addCopyModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('books.copies.bulk') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Copy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Select Book *</label>
                                <select name="book_id" class="form-select" required>
                                    <option value="">Select Book</option>
                                    @foreach($books as $book)
                                    <option value="{{ $book->book_id }}">{{ $book->title }} ({{ $book->author }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Number of Copies</label>
                                <input type="number" name="quantity" class="form-control" value="1" min="1" max="10">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Copy Number Prefix</label>
                                <input type="text" name="prefix" class="form-control" value="C-">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Starting Number</label>
                                <input type="number" name="start_from" class="form-control" value="1">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" placeholder="e.g., Shelf A-12">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Copies</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Update Modal -->
<div class="modal fade" id="bulkUpdateModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('books.copies.bulk-update') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Update Copies</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Action</label>
                        <select name="action" class="form-select">
                            <option value="change_location">Change Location</option>
                            <option value="mark_lost">Mark as Lost</option>
                            <option value="mark_available">Mark as Available</option>
                        </select>
                    </div>
                    
                    <div id="locationField" class="mb-3">
                        <label class="form-label">New Location</label>
                        <input type="text" name="new_location" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Select Copies</label>
                        <select name="copy_ids[]" class="form-select" multiple size="5">
                            @foreach($copies as $copy)
                            <option value="{{ $copy->copy_id }}">#{{ $copy->copy_id }} - {{ $copy->book->title }} ({{ $copy->copy_number }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hold Ctrl to select multiple copies</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Filter functionality
    $('#statusFilter, #bookFilter').on('change', filterTable);
    $('#searchFilter').on('keyup', filterTable);
    
    function filterTable() {
        const status = $('#statusFilter').val();
        const bookId = $('#bookFilter').val();
        const search = $('#searchFilter').val().toLowerCase();
        
        $('tbody tr').each(function() {
            const rowStatus = $(this).data('status');
            const rowBookId = $(this).data('book-id').toString();
            const rowText = $(this).text().toLowerCase();
            
            const statusMatch = !status || rowStatus === status;
            const bookMatch = !bookId || rowBookId === bookId;
            const searchMatch = !search || rowText.includes(search);
            
            $(this).toggle(statusMatch && bookMatch && searchMatch);
        });
    }
    
    $('#resetFilters').click(function() {
        $('#statusFilter, #bookFilter').val('');
        $('#searchFilter').val('');
        filterTable();
    });
    
    // Bulk update modal show/hide fields
    $('select[name="action"]').on('change', function() {
        if ($(this).val() === 'change_location') {
            $('#locationField').show();
        } else {
            $('#locationField').hide();
        }
    });
});
</script>
@endpush