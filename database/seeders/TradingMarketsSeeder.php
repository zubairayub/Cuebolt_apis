<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TradingMarketsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insert multiple market types into the trading_markets table
        DB::table('trading_markets')->insert([
            ['name' => 'Cryptocurrency', 'description' => 'Market for cryptocurrency trading', 'status' => 'active'],
            ['name' => 'Stocks', 'description' => 'Market for stock trading', 'status' => 'active'],
            ['name' => 'Forex', 'description' => 'Market for foreign exchange trading', 'status' => 'active'],
            ['name' => 'Commodities', 'description' => 'Market for commodities trading', 'status' => 'inactive'],
            ['name' => 'Bonds', 'description' => 'Market for bond trading', 'status' => 'inactive'],
            ['name' => 'Options', 'description' => 'Market for options trading', 'status' => 'active'],
            ['name' => 'Futures', 'description' => 'Market for futures trading', 'status' => 'inactive'],
            ['name' => 'ETFs', 'description' => 'Market for exchange-traded funds trading', 'status' => 'active'],
            ['name' => 'Derivatives', 'description' => 'Market for derivatives trading', 'status' => 'active'],
        ]);
    }
}
