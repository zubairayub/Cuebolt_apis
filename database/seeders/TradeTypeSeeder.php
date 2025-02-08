<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TradeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('trade_types')->insert([
            [
                'name' => 'BUY',
                'description' => 'Buying trade type',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SELL',
                'description' => 'Selling trade type',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'DCA',
                'description' => 'Dollar-Cost Averaging trade type',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
