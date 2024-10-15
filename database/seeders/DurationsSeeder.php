<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DurationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $durations = [
            ['duration_name' => 'Daily', 'duration_in_days' => 1],
            ['duration_name' => 'Weekly', 'duration_in_days' => 7],
            ['duration_name' => 'Monthly', 'duration_in_days' => 30],
            ['duration_name' => 'Yearly', 'duration_in_days' => 365],
            ['duration_name' => 'Bi-Yearly', 'duration_in_days' => 730],
        ];

        foreach ($durations as $duration) {
            DB::table('durations')->insert($duration);
        }
    }
}
