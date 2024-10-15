<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Language;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $languages = ['English', 'Urdu', 'Spanish', 'French', 'German', 'Chinese', 'Arabic'];
        
        foreach ($languages as $language) {
            Language::create(['name' => $language]);
        }
    }
}
