<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            LibrarySettingsSeeder::class,
            MembersSeeder::class,
            BooksSeeder::class,
        ]);
    }
}