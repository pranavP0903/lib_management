<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('library_settings', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();
            $table->string('setting_key')->unique();
            $table->string('setting_value');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_settings');
    }
};
