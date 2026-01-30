<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('circulation', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            $table->foreignId('member_id')
                  ->constrained('members')
                  ->cascadeOnDelete();

            $table->foreignId('copy_id')
                  ->constrained('book_copies')
                  ->cascadeOnDelete();

            $table->date('issue_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();

            $table->enum('status', ['ISSUED', 'RETURNED', 'OVERDUE'])
                  ->default('ISSUED');

            $table->unsignedInteger('renewals')->default(0);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('due_date');
            $table->index(['member_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('circulation');
    }
};
