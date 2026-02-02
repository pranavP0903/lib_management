<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Member;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Circulation;
use App\Models\Fine;

class DummyFineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a dummy student member with unique email
        $member = Member::firstOrCreate(
            ['email' => 'johndoe.library@example.com'],
            [
                'full_name' => 'John Doe',
                'member_type' => 'STUDENT',
                'phone' => '9876543210',
                'borrow_limit' => 5,
                'status' => 'ACTIVE'
            ]
        );

        // Create a dummy book if it doesn't exist
        $book = Book::firstOrCreate(
            ['isbn' => '978-0-13-468599-1'],
            [
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'category' => 'Technology',
                'publisher' => 'Prentice Hall',
                'edition' => '1st'
            ]
        );

        // Create a book copy if it doesn't exist
        $copy = BookCopy::firstOrCreate(
            ['copy_number' => 'CC-001'],
            [
                'book_id' => $book->id,
                'location' => 'Shelf A-5',
                'status' => 'ISSUED'
            ]
        );

        // Check if circulation already exists for this member and copy
        $circulation = Circulation::where('member_id', $member->id)
            ->where('copy_id', $copy->id)
            ->first();

        if (!$circulation) {
            // Create a circulation record with an overdue date (30 days ago)
            $circulation = Circulation::create([
                'member_id' => $member->id,
                'copy_id' => $copy->id,
                'issue_date' => now()->subDays(45),
                'due_date' => now()->subDays(15), // 15 days overdue
                'status' => 'ISSUED'
            ]);

            // Create a fine for the overdue book (15 days × ₹5 per day = ₹75)
            Fine::create([
                'circulation_id' => $circulation->id,
                'fine_amount' => 75.00,
                'status' => 'PENDING'
            ]);

            $this->command->info('Dummy fine data created successfully!');
        } else {
            $this->command->info('Dummy fine data already exists!');
        }

        $this->command->info('Student: John Doe');
        $this->command->info('Email: johndoe.library@example.com');
        $this->command->info('Fine Amount: ₹75.00');
    }
}
