<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();
            $table->unsignedBigInteger('hrms_user_id')->nullable();
            $table->string('full_name', 100);
            $table->enum('member_type', ['STUDENT', 'FACULTY']);
            $table->string('email', 100)->unique()->nullable();
            $table->string('phone', 15)->nullable();
            $table->unsignedInteger('borrow_limit')->default(3);
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');

            $table->timestamps();

            $table->index('member_type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
