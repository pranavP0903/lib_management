<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();
            $table->string('title', 200);
            $table->string('author', 150)->nullable();
            $table->string('isbn', 50)->unique()->nullable();
            $table->string('category', 100)->nullable();
            $table->string('publisher', 150)->nullable();
            $table->string('edition', 50)->nullable();
            $table->text('digital_resource_url')->nullable();

            $table->timestamps();

            $table->index('title');
            $table->index('author');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
