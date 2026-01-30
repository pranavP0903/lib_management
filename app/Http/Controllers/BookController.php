<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    // Display all books
    public function index(Request $request)
    {
        $query = Book::with(['copies']);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by availability
        if ($request->has('availability')) {
            if ($request->availability == 'available') {
                $query->whereHas('copies', function($q) {
                    $q->where('status', 'AVAILABLE');
                });
            } elseif ($request->availability == 'unavailable') {
                $query->whereDoesntHave('copies', function($q) {
                    $q->where('status', 'AVAILABLE');
                });
            }
        }

        $books = $query->paginate(20);
        $categories = Book::distinct()->pluck('category');
        
        $stats = [
            'total_books' => Book::count(),
            'available_copies' => BookCopy::where('status', 'AVAILABLE')->count(),
            'borrowed_copies' => BookCopy::where('status', 'ISSUED')->count(),
        ];

        return view('books.index', compact('books', 'categories', 'stats'));
    }

    // Show create book form
    public function create()
    {
        $categories = [
            'Fiction', 'Non-Fiction', 'Science', 'Technology', 'Engineering', 
            'Mathematics', 'History', 'Literature', 'Arts', 'Business', 
            'Self-Help', 'Reference'
        ];
        
        return view('books.create', compact('categories'));
    }

    // Store new book
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'author' => 'required|string|max:150',
            'isbn' => 'required|string|max:50|unique:books',
            'category' => 'required|string|max:100',
            'publisher' => 'nullable|string|max:150',
            'edition' => 'nullable|string|max:50',
            'digital_resource_url' => 'nullable|url',
        ]);

        $book = Book::create($validated);

        // Create copies if provided
        if ($request->has('copies')) {
            foreach ($request->copies as $copyData) {
                BookCopy::create([
                    'book_id' => $book->book_id,
                    'copy_number' => $copyData['copy_number'],
                    'location' => $copyData['location'] ?? null,
                    'status' => 'AVAILABLE'
                ]);
            }
        }

        AuditLog::log('BOOK_CREATE', "Book created: {$book->title}");

        return redirect()->route('books.show', $book->book_id)
            ->with('success', 'Book added successfully');
    }

    // Display single book
    public function show($id)
    {
        $book = Book::with(['copies', 'copies.currentCirculation.member', 'reservations.member'])
            ->findOrFail($id);
            
        $circulationHistory = $book->circulations()
            ->with(['member', 'copy'])
            ->orderBy('issue_date', 'desc')
            ->limit(20)
            ->get();

        $members = \App\Models\Member::where('status', 'ACTIVE')->get();

        return view('books.show', compact('book', 'circulationHistory', 'members'));
    }

    // Show edit book form
    public function edit($id)
    {
        $book = Book::findOrFail($id);
        $categories = [
            'Fiction', 'Non-Fiction', 'Science', 'Technology', 'Engineering', 
            'Mathematics', 'History', 'Literature', 'Arts', 'Business', 
            'Self-Help', 'Reference'
        ];
        
        return view('books.edit', compact('book', 'categories'));
    }

    // Update book
    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'author' => 'required|string|max:150',
            'isbn' => 'required|string|max:50|unique:books,isbn,' . $id . ',book_id',
            'category' => 'required|string|max:100',
            'publisher' => 'nullable|string|max:150',
            'edition' => 'nullable|string|max:50',
            'digital_resource_url' => 'nullable|url',
        ]);

        $book->update($validated);

        AuditLog::log('BOOK_UPDATE', "Book updated: {$book->title}");

        return redirect()->route('books.show', $book->book_id)
            ->with('success', 'Book updated successfully');
    }

    // Delete book
    public function destroy($id)
    {
        $book = Book::findOrFail($id);
        $title = $book->title;
        $book->delete();

        AuditLog::log('BOOK_DELETE', "Book deleted: {$title}");

        return redirect()->route('books.index')
            ->with('success', 'Book deleted successfully');
    }

    // AJAX search for books
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        $books = Book::withCount(['copies as available_copies' => function($q) {
            $q->where('status', 'AVAILABLE');
        }])
        ->where('title', 'like', "%{$query}%")
        ->orWhere('author', 'like', "%{$query}%")
        ->orWhere('isbn', 'like', "%{$query}%")
        ->limit(10)
        ->get()
        ->map(function ($book) {
            return [
                'book_id' => $book->book_id,
                'title' => $book->title,
                'author' => $book->author,
                'isbn' => $book->isbn,
                'available_copies' => $book->available_copies,
                'category' => $book->category
            ];
        });

        return response()->json($books);
    }

    // Get available copies for a book
    public function availableCopies(Request $request)
    {
        $bookId = $request->get('book_id');
        
        $copies = BookCopy::where('book_id', $bookId)
            ->where('status', 'AVAILABLE')
            ->get(['copy_id', 'copy_number', 'location']);
            
        return response()->json($copies);
    }

    // Manage copies
    public function copies(Request $request)
    {
        $query = BookCopy::with(['book', 'currentCirculation.member']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by book
        if ($request->has('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        $copies = $query->paginate(30);
        $books = Book::all();
        
        $stats = [
            'available' => BookCopy::where('status', 'AVAILABLE')->count(),
            'issued' => BookCopy::where('status', 'ISSUED')->count(),
            'reserved' => BookCopy::where('status', 'RESERVED')->count(),
            'lost' => BookCopy::where('status', 'LOST')->count(),
        ];

        return view('books.copies', compact('copies', 'books', 'stats'));
    }
}