<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('book_copies', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            $table->foreignId('book_id')
                  ->constrained('books')
                  ->cascadeOnDelete();

            $table->string('copy_number', 50);
            $table->enum('status', ['AVAILABLE', 'ISSUED', 'RESERVED', 'LOST'])
                  ->default('AVAILABLE');
            $table->string('location', 100)->nullable();

            $table->timestamps();

            $table->unique(['book_id', 'copy_number']);
            $table->index('status');
            $table->index(['book_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_copies');
    }
};
