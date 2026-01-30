@extends('layouts.app')

@section('title', 'Book Catalog')
@section('subtitle', 'Browse and manage library books')

@section('header-buttons')
    <a href="{{ route('books.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Book
    </a>
@endsection

@section('content')
<!-- Search and Filter Card -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="{{ route('books.index') }}" method="GET" class="row g-3">
            <div class="col-md-5">
                <div class="search-box">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by title, author, ISBN..." 
                           value="{{ request('search') }}">
                    <i class="bi bi-search"></i>
                </div>
            </div>
            <div class="col-md-3">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                            {{ $category }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="availability" class="form-select">
                    <option value="">All</option>
                    <option value="available" {{ request('availability') == 'available' ? 'selected' : '' }}>
                        Available
                    </option>
                    <option value="unavailable" {{ request('availability') == 'unavailable' ? 'selected' : '' }}>
                        Unavailable
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

<!-- Books Grid -->
<div class="row">
    @forelse($books as $book)
    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
        <div class="card book-card h-100 shadow-sm">
            <div class="card-body">
                <!-- Book Cover/Icon -->
                <div class="text-center mb-3">
                    <div class="bg-light rounded p-4 mb-3 mx-auto" style="width: 120px; height: 160px;">
                        @if($book->digital_resource_url)
                            <i class="bi bi-file-earmark-pdf text-primary fs-1"></i>
                            <div class="mt-2">
                                <span class="badge bg-info">Digital</span>
                            </div>
                        @else
                            <i class="bi bi-book text-primary fs-1"></i>
                            <div class="mt-2">
                                <span class="badge bg-secondary">Physical</span>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Book Info -->
                <h5 class="card-title text-truncate">{{ $book->title }}</h5>
                <p class="card-text text-muted small mb-2">
                    <i class="bi bi-person me-1"></i>{{ $book->author }}
                </p>
                
                <!-- Book Details -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">ISBN:</span>
                        <span class="fw-bold">{{ $book->isbn }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Category:</span>
                        <span class="badge bg-info">{{ $book->category }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Available:</span>
                        <span class="badge bg-{{ $book->availableCopies() > 0 ? 'success' : 'danger' }}">
                            {{ $book->availableCopies() }} of {{ $book->copies->count() }}
                        </span>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="d-grid gap-2">
                    <a href="{{ route('books.show', $book->book_id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye me-1"></i> View Details
                    </a>
                    <div class="btn-group" role="group">
                        <a href="{{ route('books.edit', $book->book_id) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                onclick="confirmDelete('delete-book-{{ $book->book_id }}')">
                            <i class="bi bi-trash"></i>
                        </button>
                        <form id="delete-book-{{ $book->book_id }}" 
                              action="{{ route('books.destroy', $book->book_id) }}" 
                              method="POST" class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white">
                <small class="text-muted">
                    <i class="bi bi-calendar me-1"></i>
                    Added {{ $book->created_at->diffForHumans() }}
                </small>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card shadow">
            <div class="card-body text-center py-5">
                <i class="bi bi-book fs-1 text-muted"></i>
                <h4 class="mt-3">No books found</h4>
                <p class="text-muted">Try adjusting your search or add a new book</p>
                <a href="{{ route('books.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Add First Book
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($books->hasPages())
<div class="row mt-4">
    <div class="col-12">
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                {{ $books->links() }}
            </ul>
        </nav>
    </div>
</div>
@endif

<!-- Quick Stats -->
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card bg-light border-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-primary text-white rounded p-3 me-3">
                        <i class="bi bi-book fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">Total Titles</h6>
                        <h3 class="mb-0">{{ $total_books }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-light border-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-success text-white rounded p-3 me-3">
                        <i class="bi bi-check-circle fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">Available Copies</h6>
                        <h3 class="mb-0">{{ $available_copies }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-light border-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-warning text-white rounded p-3 me-3">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">Borrowed Copies</h6>
                        <h3 class="mb-0">{{ $borrowed_copies }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .book-card {
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .book-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
</style>
@endpush