<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PackagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $packages = [
            [
                'user_id' => 35,  // Trader 1
                'duration_id' => 1,  // Daily
                'name' => 'Daily Signals Pack',
                'description' => 'Receive 5 crypto signals for 24 hours.',
                'signals_count' => 5,  // Limit to 5 signals
                'price' => 50.00,
                'picture' => 'https://images.unsplash.com/photo-1521845601031-325d3a07cc2e?crop=entropy&cs=tinysrgb&fit=max&ixid=MnwzNjUyOXwwfDF8c2VhcmNofDV8Y3J5cHRvJTIwc2lnbmFsc3xlbnwwfHx8fDE2NzgxNjk1Mjk&ixlib=rb-1.2.1&q=80&w=1080',  // Live image from Unsplash
                'risk_reward_ratio' => 2.0,  // Example RRR value
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 54,  // Trader 2
                'duration_id' => 2,  // Weekly
                'name' => 'Weekly Signals Pack',
                'description' => 'Receive 20 crypto signals for the week with 30% RRR.',
                'signals_count' => 20,
                'price' => 120.00,
                'picture' => 'https://images.unsplash.com/photo-1571171637578-5e3be9d1b2e0?crop=entropy&cs=tinysrgb&fit=max&ixid=MnwzNjUyOXwwfDF8c2VhcmNofDYyfGNyeXB0byUyMHN1cHBvcnR8ZW58MHx8fHwxNjc4MTY5NTg2&ixlib=rb-1.2.1&q=80&w=1080',  // Live image from Unsplash
                'risk_reward_ratio' => 2.0,  // Example RRR value
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 55,  // Trader 3
                'duration_id' => 3,  // Monthly
                'name' => 'Monthly Premium Signals Pack',
                'description' => 'Receive unlimited crypto signals for a month with 50% RRR.',
                'signals_count' => 0,  // Unlimited signals
                'price' => 300.00,
                'picture' => 'https://images.unsplash.com/photo-1593642632889-92a1a3c5f1d3?crop=entropy&cs=tinysrgb&fit=max&ixid=MnwzNjUyOXwwfDF8c2VhcmNofDV8Y3J5cHRvJTIwc2lnbmFsc3xlbnwwfHx8fDE2NzgxNzE3NzA&ixlib=rb-1.2.1&q=80&w=1080',  // Live image from Unsplash
                'risk_reward_ratio' => 2.0,  // Example RRR value
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 47,  // Trader 4
                'duration_id' => 4,  // Yearly
                'name' => 'Yearly Signals Pack',
                'description' => 'Receive unlimited signals for the entire year with guaranteed 60% RRR.',
                'signals_count' => 0,  // Unlimited signals
                'price' => 1000.00,
                'picture' => 'https://images.unsplash.com/photo-1564866653-dc6a8e1f95d0?crop=entropy&cs=tinysrgb&fit=max&ixid=MnwzNjUyOXwwfDF8c2VhcmNofDZ8Y3J5cHRvJTIwc2lnbmFsc3xlbnwwfHx8fDE2NzgxNzE5Mzk&ixlib=rb-1.2.1&q=80&w=1080',  // Live image from Unsplash
                'risk_reward_ratio' => 2.0,  // Example RRR value
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        foreach ($packages as $package) {
            DB::table('packages')->insert($package);
        }
    }
}
