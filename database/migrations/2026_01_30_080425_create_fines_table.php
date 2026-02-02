<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fines', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            $table->foreignId('circulation_id')
                  ->constrained('circulation')
                  ->cascadeOnDelete();

            $table->decimal('fine_amount', 10, 2)->default(0.00);
            $table->enum('status', ['PENDING', 'PAID', 'WAIVED'])
                  ->default('PENDING');

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fines');
    }
};
