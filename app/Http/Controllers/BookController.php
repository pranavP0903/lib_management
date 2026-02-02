<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Member;
use App\Models\Circulation;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;

class BookController extends Controller
{
    /**
     * Display all books
     */
    public function index(Request $request)
    {
        $query = Book::with('copies');

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('author', 'like', '%' . $request->search . '%')
                  ->orWhere('isbn', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by availability
        if ($request->filled('availability')) {
            if ($request->availability === 'available') {
                $query->whereHas('copies', fn ($q) =>
                    $q->where('status', 'AVAILABLE')
                );
            }

            if ($request->availability === 'unavailable') {
                $query->whereDoesntHave('copies', fn ($q) =>
                    $q->where('status', 'AVAILABLE')
                );
            }
        }

        $books = $query->orderBy('title')->paginate(12);

        // Filters
        $categories = Book::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category');

        // Stats USED IN BLADE
        $total_books = Book::count();
        $available_copies = BookCopy::where('status', 'AVAILABLE')->count();
        $borrowed_copies = BookCopy::where('status', 'ISSUED')->count();

        return view('books.index', compact(
            'books',
            'categories',
            'total_books',
            'available_copies',
            'borrowed_copies'
        ));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $categories = [
            'Fiction', 'Non-Fiction', 'Science', 'Technology',
            'Engineering', 'Mathematics', 'History', 'Literature',
            'Arts', 'Business', 'Self-Help', 'Reference'
        ];

        return view('books.create', compact('categories'));
    }

    /**
     * Store book
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'author' => 'required|string|max:150',
            'isbn' => 'required|string|max:50|unique:books,isbn',
            'category' => 'required|string|max:100',
            'publisher' => 'nullable|string|max:150',
            'edition' => 'nullable|string|max:50',
            'digital_resource_url' => 'nullable|url',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'release_mode' => 'nullable|in:INSTANT,SCHEDULED',
            'release_at' => 'nullable|date|required_if:release_mode,SCHEDULED',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('books', 'public');
            $validated['image_path'] = $imagePath;
        }

        // default release_mode
        if (empty($validated['release_mode'])) {
            $validated['release_mode'] = 'INSTANT';
        }

        $book = Book::create($validated);

        if ($request->filled('copies')) {
            foreach ($request->copies as $copy) {
                BookCopy::create([
                    'book_id' => $book->id,
                    'copy_number' => $copy['copy_number'],
                    'location' => $copy['location'] ?? null,
                    'status' => 'AVAILABLE',
                ]);
            }
        }

        AuditLog::log('BOOK_CREATE', "Book created: {$book->title}");

        return redirect()
            ->route('books.show', $book)
            ->with('success', 'Book added successfully');
    }

    /**
     * Show book details
     */
    public function show(Book $book)
    {
        $book->load([
            'copies',
            'copies.currentCirculation.member',
            'reservations.member'
        ]);

        $circulationHistory = Circulation::whereHas('copy', function($q) use ($book) {
                $q->where('book_id', $book->id);
            })
            ->with(['member', 'copy'])
            ->orderByDesc('issue_date')
            ->limit(20)
            ->get();

        $members = Member::where('status', 'ACTIVE')->get();

        return view('books.show', compact(
            'book',
            'circulationHistory',
            'members'
        ));
    }

    public function storeCopies(Request $request)
{
    $request->validate([
        'book_id' => 'required|exists:books,id',
        'copies'  => 'required|integer|min:1|max:50',
    ]);

    for ($i = 1; $i <= $request->copies; $i++) {
        BookCopy::create([
            'book_id' => $request->book_id,
            'status'  => 'AVAILABLE',
        ]);
    }

    return redirect()
        ->route('books.copies', ['book_id' => $request->book_id])
        ->with('success', 'Book copies added successfully.');
}

public function bulkUpdateCopies(Request $request)
{
    $request->validate([
        'copy_ids' => 'required|array',
        'status'   => 'required|in:AVAILABLE,ISSUED,LOST,DAMAGED',
    ]);

    BookCopy::whereIn('id', $request->copy_ids)
        ->update([
            'status' => $request->status,
        ]);

    return back()->with('success', 'Book copies updated successfully.');
}

    /**
     * Edit form
     */
    public function edit(Book $book)
    {
        $categories = [
            'Fiction', 'Non-Fiction', 'Science', 'Technology',
            'Engineering', 'Mathematics', 'History', 'Literature',
            'Arts', 'Business', 'Self-Help', 'Reference'
        ];

        return view('books.edit', compact('book', 'categories'));
    }

    /**
     * Update book
     */
    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'author' => 'required|string|max:150',
            'isbn' => 'required|string|max:50|unique:books,isbn,' . $book->id,
            'category' => 'required|string|max:100',
            'publisher' => 'nullable|string|max:150',
            'edition' => 'nullable|string|max:50',
            'digital_resource_url' => 'nullable|url',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'release_mode' => 'nullable|in:INSTANT,SCHEDULED',
            'release_at' => 'nullable|date|required_if:release_mode,SCHEDULED',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($book->image_path && \Storage::disk('public')->exists($book->image_path)) {
                \Storage::disk('public')->delete($book->image_path);
            }
            $imagePath = $request->file('image')->store('books', 'public');
            $validated['image_path'] = $imagePath;
        }

        // ensure release_mode default
        if (empty($validated['release_mode'])) {
            $validated['release_mode'] = 'INSTANT';
            $validated['release_at'] = null;
        }

        $book->update($validated);

        AuditLog::log('BOOK_UPDATE', "Book updated: {$book->title}");

        return redirect()
            ->route('books.show', $book)
            ->with('success', 'Book updated successfully');
    }

    /**
     * Delete book
     */
    public function destroy(Book $book)
    {
        $title = $book->title;
        
        // Delete image if exists
        if ($book->image_path && \Storage::disk('public')->exists($book->image_path)) {
            \Storage::disk('public')->delete($book->image_path);
        }
        
        $book->delete();

        AuditLog::log('BOOK_DELETE', "Book deleted: {$title}");

        return redirect()
            ->route('books.index')
            ->with('success', 'Book deleted successfully');
    }

    /**
     * AJAX search
     */
    public function search(Request $request)
{
    $q = $request->q;

    $books = Book::withCount([
            'copies as available_copies' => function ($q) {
                $q->where('status', 'AVAILABLE');
            }
        ])
        ->where('title', 'like', "%$q%")
        ->orWhere('author', 'like', "%$q%")
        ->orWhere('isbn', 'like', "%$q%")
        ->limit(10)
        ->get();

    return response()->json(
        $books->map(function ($b) {
            return [
                'id' => $b->id,
                'title' => $b->title,
                'author' => $b->author ?? '',
                'isbn' => $b->isbn ?? '',
                'category' => $b->category ?? '',
                'available_copies' => $b->available_copies ?? 0,
            ];
        })
    );
}



    /**
     * Get available copies
     */
    public function availableCopies(Request $request)
{
    return response()->json(
        BookCopy::where('book_id', $request->book_id)
            ->where('status', 'AVAILABLE')
            ->get(['id', 'copy_number'])
    );
}


    /**
     * Manage copies
     */
    public function copies(Request $request)
    {
        $query = BookCopy::with(['book', 'currentCirculation.member']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        $copies = $query->paginate(30);
        $books = Book::all();

        $stats = [
            'available' => BookCopy::where('status', 'AVAILABLE')->count(),
            'issued'    => BookCopy::where('status', 'ISSUED')->count(),
            'reserved'  => BookCopy::where('status', 'RESERVED')->count(),
            'lost'      => BookCopy::where('status', 'LOST')->count(),
        ];

        return view('books.copies', compact('copies', 'books', 'stats'));
    }

    /**
     * Store a new book copy
     */
    public function storeCopy(Request $request, Book $book): RedirectResponse
    {
        $validated = $request->validate([
            'copy_number' => 'required|string|max:50',
            'location' => 'nullable|string|max:100',
        ]);

        $book->copies()->create([
            'copy_number' => $validated['copy_number'],
            'location' => $validated['location'] ?? null,
            'status' => 'AVAILABLE',
        ]);

        return redirect()->back()->with('success', 'Copy added');
    }

    /**
     * Update a book copy
     */
    public function updateCopy(Request $request, BookCopy $copy): RedirectResponse
    {
        $validated = $request->validate([
            'copy_number' => 'required|string|max:50',
            'location' => 'nullable|string|max:100',
            'status' => 'required|in:AVAILABLE,ISSUED,RESERVED,LOST',
        ]);

        $copy->update($validated);

        return redirect()->back()->with('success', 'Copy updated');
    }

    /**
     * Destroy a book copy
     */
    public function destroyCopy(BookCopy $copy): RedirectResponse
    {
        if ($copy->status !== 'AVAILABLE') {
            return redirect()->back()->with('error', 'Only available copies can be deleted');
        }

        $copy->delete();
        return redirect()->back()->with('success', 'Copy deleted');
    }
}
