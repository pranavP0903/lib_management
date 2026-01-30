<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\LibrarySetting;
use Illuminate\Support\Str;

class LibrarySeeder extends Seeder
{
    public function run()
    {
        // Insert default settings
        LibrarySetting::insert([
            ['setting_key' => 'BORROW_DAYS_STUDENT', 'setting_value' => '7'],
            ['setting_key' => 'BORROW_DAYS_FACULTY', 'setting_value' => '14'],
            ['setting_key' => 'FINE_PER_DAY', 'setting_value' => '5'],
            ['setting_key' => 'MAX_RESERVATIONS', 'setting_value' => '2'],
            ['setting_key' => 'MAX_BOOKS_STUDENT', 'setting_value' => '3'],
            ['setting_key' => 'MAX_BOOKS_FACULTY', 'setting_value' => '5'],
            ['setting_key' => 'GRACE_PERIOD', 'setting_value' => '2'],
        ]);

        // Create sample members
        $students = [
            ['hrms_user_id' => 1001, 'full_name' => 'John Doe', 'email' => 'john@example.com', 'member_type' => 'STUDENT'],
            ['hrms_user_id' => 1002, 'full_name' => 'Jane Smith', 'email' => 'jane@example.com', 'member_type' => 'STUDENT'],
            ['hrms_user_id' => 1003, 'full_name' => 'Robert Johnson', 'email' => 'robert@example.com', 'member_type' => 'STUDENT'],
        ];

        $faculty = [
            ['hrms_user_id' => 2001, 'full_name' => 'Dr. Sarah Williams', 'email' => 'sarah@example.com', 'member_type' => 'FACULTY', 'borrow_limit' => 5],
            ['hrms_user_id' => 2002, 'full_name' => 'Prof. Michael Brown', 'email' => 'michael@example.com', 'member_type' => 'FACULTY', 'borrow_limit' => 5],
        ];

        foreach ($students as $student) {
            Member::create(array_merge($student, [
                'phone' => '9876543210',
                'borrow_limit' => 3,
                'status' => 'ACTIVE'
            ]));
        }

        foreach ($faculty as $facultyMember) {
            Member::create(array_merge($facultyMember, [
                'phone' => '9876543211',
                'status' => 'ACTIVE'
            ]));
        }

        // Create sample books
        $books = [
            ['title' => 'Introduction to Algorithms', 'author' => 'Thomas H. Cormen', 'isbn' => '9780262033848', 'category' => 'Computer Science'],
            ['title' => 'Clean Code', 'author' => 'Robert C. Martin', 'isbn' => '9780132350884', 'category' => 'Programming'],
            ['title' => 'The Pragmatic Programmer', 'author' => 'Andrew Hunt', 'isbn' => '9780201616224', 'category' => 'Programming'],
            ['title' => 'Design Patterns', 'author' => 'Erich Gamma', 'isbn' => '9780201633610', 'category' => 'Software Engineering'],
            ['title' => 'Database System Concepts', 'author' => 'Abraham Silberschatz', 'isbn' => '9780078022159', 'category' => 'Database'],
        ];

        foreach ($books as $bookData) {
            $book = Book::create($bookData);

            // Create 2-3 copies for each book
            for ($i = 1; $i <= rand(2, 3); $i++) {
                BookCopy::create([
                    'book_id' => $book->book_id,
                    'copy_number' => 'C-' . str_pad($book->book_id, 3, '0', STR_PAD_LEFT) . '-' . $i,
                    'status' => 'AVAILABLE',
                    'location' => 'Shelf ' . chr(65 + rand(0, 5)) . '-' . rand(1, 20)
                ]);
            }
        }
    }
}