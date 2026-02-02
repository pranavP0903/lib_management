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

                <!-- Book Icon -->
                <div class="text-center mb-3">
                    @if($book->image_path && file_exists(public_path('storage/' . $book->image_path)))
                        <div class="bg-light rounded p-0 mb-3 mx-auto" style="width:120px;height:160px;overflow:hidden;">
                            <img src="{{ asset('storage/' . $book->image_path) }}" alt="{{ $book->title }}" 
                                 style="width:100%;height:100%;object-fit:cover;">
                        </div>
                    @else
                        <div class="bg-light rounded p-4 mb-3 mx-auto" style="width:120px;height:160px;">
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
                    @endif
                </div>

                <!-- Book Info -->
                <h5 class="card-title text-truncate">{{ $book->title }}</h5>
                <p class="card-text text-muted small mb-2">
                    <i class="bi bi-person me-1"></i>{{ $book->author }}
                </p>

                <!-- Details -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">ISBN:</span>
                        <span class="fw-bold">{{ $book->isbn }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Category:</span>
                        <span class="badge bg-info">{{ $book->category }}</span>
                    </div>
                    @if(!$book->isReleased())
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Status:</span>
                            <span class="badge bg-secondary">Coming on {{ $book->release_at?->format('M d, Y H:i') }}</span>
                        </div>
                    @else
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Available:</span>
                            <span class="badge bg-{{ $book->availableCopies() > 0 ? 'success' : 'danger' }}">
                                {{ $book->availableCopies() }} of {{ $book->copies->count() }}
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="d-grid gap-2">
                    <a href="{{ route('books.show', $book) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye me-1"></i> View Details
                    </a>

                    <div class="btn-group" role="group">
                        <a href="{{ route('books.edit', $book) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-pencil"></i>
                        </a>

                        <form id="delete-book-{{ $book->id }}"
                              action="{{ route('books.destroy', $book) }}"
                              method="POST"
                              onsubmit="return confirm('Are you sure you want to delete this book?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-trash"></i>
                            </button>
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
        {{ $books->links() }}
    </div>
</div>
@endif

<!-- Quick Stats -->
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card bg-light border-0">
            <div class="card-body">
                <h6>Total Titles</h6>
                <h3>{{ $total_books }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-light border-0">
            <div class="card-body">
                <h6>Available Copies</h6>
                <h3>{{ $available_copies }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-light border-0">
            <div class="card-body">
                <h6>Borrowed Copies</h6>
                <h3>{{ $borrowed_copies }}</h3>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.book-card {
    transition: transform .3s, box-shadow .3s;
}
.book-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,.1)!important;
}
</style>
@endpush
