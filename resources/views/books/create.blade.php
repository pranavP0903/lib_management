@extends('layouts.app')

@section('title', 'Add New Book')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Add New Book</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('books.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
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
                                           placeholder="Enter book title">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Edition</label>
                                    <input type="text" name="edition" class="form-control" 
                                           placeholder="e.g., 1st, 2nd">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Author *</label>
                                    <input type="text" name="author" class="form-control" required 
                                           placeholder="Author name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">ISBN *</label>
                                    <input type="text" name="isbn" class="form-control" required 
                                           placeholder="International Standard Book Number">
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
                                        <option value="Fiction">Fiction</option>
                                        <option value="Non-Fiction">Non-Fiction</option>
                                        <option value="Science">Science</option>
                                        <option value="Technology">Technology</option>
                                        <option value="Engineering">Engineering</option>
                                        <option value="Mathematics">Mathematics</option>
                                        <option value="History">History</option>
                                        <option value="Literature">Literature</option>
                                        <option value="Arts">Arts</option>
                                        <option value="Business">Business</option>
                                        <option value="Self-Help">Self-Help</option>
                                        <option value="Reference">Reference</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Publisher</label>
                                    <input type="text" name="publisher" class="form-control" 
                                           placeholder="Publisher name">
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
                                   placeholder="https://example.com/digital-book.pdf">
                            <small class="text-muted">Provide URL for digital version (PDF, eBook, etc.)</small>
                        </div>
                    </div>

                    <!-- Book Image -->
                    <div class="mb-4">
                        <h6 class="mb-3 text-primary">
                            <i class="bi bi-image me-2"></i>Book Image (Optional)
                        </h6>
                        <div class="mb-3">
                            <label class="form-label">Upload Book Cover</label>
                            <input type="file" name="image" class="form-control" accept="image/*" 
                                   id="imageInput" onchange="previewImage(event)">
                            <small class="text-muted">Upload a book cover image (JPG, PNG, WEBP - Max 2MB)</small>
                        </div>
                        <div id="imagePreview" style="display: none;" class="mb-3">
                            <img id="previewImg" src="" alt="Preview" style="max-width: 200px; max-height: 250px;" class="border rounded">
                        </div>
                    </div>
                    

                    <!-- Book Copies -->
                    <div class="mb-4">
                        <h6 class="mb-3 text-primary">
                            <i class="bi bi-copy me-2"></i>Physical Copies
                        </h6>
                        <div id="copies-container">
                            <!-- First copy will be added here -->
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">Add physical copies for lending</small>
                            <button type="button" id="add-copy" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-plus-circle me-1"></i> Add Copy
                            </button>
                        </div>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('books.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Save Book
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let copyCount = 0;

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
    
    // Add copy template
    function addCopyField() {
        copyCount++;
        const template = `
        <div class="copy-item card border mb-3" id="copy-${copyCount}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Copy #${copyCount}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-copy" 
                            onclick="removeCopy(${copyCount})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Copy Number *</label>
                            <input type="text" name="copies[${copyCount}][copy_number]" 
                                   class="form-control" required placeholder="e.g., C-001">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="copies[${copyCount}][location]" 
                                   class="form-control" placeholder="e.g., Shelf A-12">
                        </div>
                    </div>
                </div>
            </div>
                    
                    <!-- Release / Launch Options -->
                    <div class="mb-4">
                        <h6 class="mb-3 text-primary">
                            <i class="bi bi-rocket2 me-2"></i>Launch Options
                        </h6>
                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="release_mode" id="releaseInstant" value="INSTANT" checked onclick="toggleReleaseAt(false)">
                                <label class="form-check-label" for="releaseInstant">Launch Immediately</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="release_mode" id="releaseSchedule" value="SCHEDULED" onclick="toggleReleaseAt(true)">
                                <label class="form-check-label" for="releaseSchedule">Schedule Launch</label>
                            </div>
                        </div>

                        <div class="mb-3" id="releaseAtContainer" style="display: none;">
                            <label class="form-label">Launch Date & Time</label>
                            <input type="datetime-local" name="release_at" class="form-control">
                            <small class="text-muted">Choose date & time when the book should become available in the catalog.</small>
                        </div>
                    </div>
        </div>
        `;
        
        $('#copies-container').append(template);
    }
    
    // Remove copy
    function removeCopy(id) {
        $(`#copy-${id}`).remove();
        // Re-number remaining copies
        renumberCopies();
    }
    
    // Re-number copies
    function renumberCopies() {
        const copies = $('.copy-item');
        copyCount = copies.length;
        copies.each(function(index) {
            const newIndex = index + 1;
            $(this).attr('id', `copy-${newIndex}`);
            $(this).find('h6').text(`Copy #${newIndex}`);
            $(this).find('[name^="copies"]').each(function() {
                const name = $(this).attr('name').replace(/\[\d+\]/, `[${newIndex}]`);
                $(this).attr('name', name);
            });
        });
    }
    
    // Toggle release at input
    function toggleReleaseAt(show) {
        const container = document.getElementById('releaseAtContainer');
        if (!container) return;
        container.style.display = show ? 'block' : 'none';
    }

    // Initialize first copy
    $(document).ready(function() {
        addCopyField();
        
        $('#add-copy').click(function() {
            addCopyField();
        });
    });
</script>
@endpush