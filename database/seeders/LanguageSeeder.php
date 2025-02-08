<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            ['name' => 'English'],
            ['name' => 'Spanish'],
            ['name' => 'French'],
            ['name' => 'German'],
            ['name' => 'Chinese'],
            ['name' => 'Arabic'],
            ['name' => 'Hindi'],
            ['name' => 'Portuguese'],
            ['name' => 'Russian'],
            ['name' => 'Japanese'],
        ];

        foreach ($languages as $language) {
            Language::firstOrCreate($language);
        }
    }
}
