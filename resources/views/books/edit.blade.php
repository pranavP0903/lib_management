@extends('layouts.app')

@section('title', 'Edit Book')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Edit Book: {{ $book->title }}</h5>
                <a href="{{ route('books.show', $book->id) }}" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-eye me-1"></i> View
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('books.update', $book->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <!-- Basic Information -->
                    <div class="mb-4">
                        <h6 class="mb-3 text-primary">
                            <i class="bi bi-info-circle me-2"></i>Basic Information
                        </h6>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Title *</label>
                                    <input type="text" name="title" class="form-control" required 
                                           value="{{ old('title', $book->title) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Edition</label>
                                    <input type="text" name="edition" class="form-control" 
                                           value="{{ old('edition', $book->edition) }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Author *</label>
                                    <input type="text" name="author" class="form-control" required 
                                           value="{{ old('author', $book->author) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">ISBN *</label>
                                    <input type="text" name="isbn" class="form-control" required 
                                           value="{{ old('isbn', $book->isbn) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Category & Publisher -->
                    <div class="mb-4">
                        <h6 class="mb-3 text-primary">
                            <i class="bi bi-tags me-2"></i>Classification
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Category *</label>
                                    <select name="category" class="form-select" required>
                                        <option value="">Select Category</option>
                                        <option value="Fiction" {{ old('category', $book->category) == 'Fiction' ? 'selected' : '' }}>Fiction</option>
                                        <option value="Non-Fiction" {{ old('category', $book->category) == 'Non-Fiction' ? 'selected' : '' }}>Non-Fiction</option>
                                        <option value="Science" {{ old('category', $book->category) == 'Science' ? 'selected' : '' }}>Science</option>
                                        <option value="Technology" {{ old('category', $book->category) == 'Technology' ? 'selected' : '' }}>Technology</option>
                                        <option value="Engineering" {{ old('category', $book->category) == 'Engineering' ? 'selected' : '' }}>Engineering</option>
                                        <option value="Mathematics" {{ old('category', $book->category) == 'Mathematics' ? 'selected' : '' }}>Mathematics</option>
                                        <option value="History" {{ old('category', $book->category) == 'History' ? 'selected' : '' }}>History</option>
                                        <option value="Literature" {{ old('category', $book->category) == 'Literature' ? 'selected' : '' }}>Literature</option>
                                        <option value="Arts" {{ old('category', $book->category) == 'Arts' ? 'selected' : '' }}>Arts</option>
                                        <option value="Business" {{ old('category', $book->category) == 'Business' ? 'selected' : '' }}>Business</option>
                                        <option value="Self-Help" {{ old('category', $book->category) == 'Self-Help' ? 'selected' : '' }}>Self-Help</option>
                                        <option value="Reference" {{ old('category', $book->category) == 'Reference' ? 'selected' : '' }}>Reference</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Publisher</label>
                                    <input type="text" name="publisher" class="form-control" 
                                           value="{{ old('publisher', $book->publisher) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Digital Resource -->
                    <div class="mb-4">
                        <h6 class="mb-3 text-primary">
                            <i class="bi bi-link me-2"></i>Digital Resource (Optional)
                        </h6>
                        <div class="mb-3">
                            <label class="form-label">Digital Resource URL</label>
                            <input type="url" name="digital_resource_url" class="form-control" 
                                   value="{{ old('digital_resource_url', $book->digital_resource_url) }}">
                            <small class="text-muted">Provide URL for digital version (PDF, eBook, etc.)</small>
                        </div>
                    </div>

                    <!-- Release / Launch Options -->
                    <div class="mb-4">
                        <h6 class="mb-3 text-primary">
                            <i class="bi bi-rocket2 me-2"></i>Launch Options
                        </h6>
                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="release_mode" id="releaseInstant" value="INSTANT" {{ $book->release_mode == 'INSTANT' ? 'checked' : '' }} onclick="toggleReleaseAt(true)">
                                <label class="form-check-label" for="releaseInstant">Launch Immediately</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="release_mode" id="releaseSchedule" value="SCHEDULED" {{ $book->release_mode == 'SCHEDULED' ? 'checked' : '' }} onclick="toggleReleaseAt(true)">
                                <label class="form-check-label" for="releaseSchedule">Schedule Launch</label>
                            </div>
                        </div>

                        <div class="mb-3" id="releaseAtContainer" style="display: none;">
                            <label class="form-label">Launch Date & Time</label>
                            <input type="datetime-local" name="release_at" class="form-control" value="{{ optional($book->release_at)->format('Y-m-d\TH:i') }}">
                            <small class="text-muted">Choose date & time when the book should become available in the catalog.</small>
                        </div>
                    </div>

                    <!-- Book Image -->
                    <div class="mb-4">
                        <h6 class="mb-3 text-primary">
                            <i class="bi bi-image me-2"></i>Book Image (Optional)
                        </h6>
                        @if($book->image_path)
                            <div class="mb-3">
                                <label class="form-label">Current Image</label>
                                <div>
                                    <img src="{{ asset('storage/' . $book->image_path) }}" alt="{{ $book->title }}" 
                                         style="max-width: 200px; max-height: 250px;" class="border rounded">
                                </div>
                            </div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Upload New Book Cover</label>
                            <input type="file" name="image" class="form-control" accept="image/*" 
                                   id="imageInput" onchange="previewImage(event)">
                            <small class="text-muted">Upload a book cover image (JPG, PNG, WEBP - Max 2MB)</small>
                        </div>
                        <div id="imagePreview" style="display: none;" class="mb-3">
                            <img id="previewImg" src="" alt="Preview" style="max-width: 200px; max-height: 250px;" class="border rounded">
                        </div>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('books.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Cancel
                        </a>
                        <div class="btn-group">
                            <a href="{{ route('books.show', $book->id) }}" class="btn btn-outline-info">
                                <i class="bi bi-eye me-1"></i> View
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Update Book
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Book Copies Management -->
        <div class="card shadow mt-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Manage Copies</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Copy ID</th>
                                <th>Copy Number</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($book->copies as $copy)
                            <tr>
                                <td>#{{ $copy->copy_id }}</td>
                                <td>{{ $copy->copy_number }}</td>
                                <td>{{ $copy->location ?? 'Not specified' }}</td>
                                <td>
                                    <span class="badge bg-{{ $copy->status == 'AVAILABLE' ? 'success' : 'warning' }}">
                                        {{ $copy->status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary" 
                                                            data-bs-toggle="modal" data-bs-target="#editCopyModal{{ $copy->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                                    <form action="{{ route('books.copies.destroy', $copy->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" 
                                                    onclick="return confirm('Are you sure?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
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
                                                            <label class="form-label">Copy Number</label>
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
                
                <!-- Add New Copy -->
                <div class="mt-4">
                    <h6>Add New Copy</h6>
                          <form action="{{ route('books.copies.store', $book->id) }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-4">
                            <input type="text" name="copy_number" class="form-control" placeholder="Copy Number" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="location" class="form-control" placeholder="Location">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="bi bi-plus-circle me-1"></i> Add Copy
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Preview image
    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    }

    function toggleReleaseAt(show) {
        const container = document.getElementById('releaseAtContainer');
        if (!container) return;
        container.style.display = show ? 'block' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        // show/hide based on current book value
        const mode = '{{ $book->release_mode ?? 'INSTANT' }}';
        toggleReleaseAt(mode === 'SCHEDULED');
    });
</script>
@endpush
@endsection