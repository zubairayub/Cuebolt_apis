<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            CountryCitySeeder::class,
            DurationsSeeder::class,
            LanguageSeeder::class,
            TradingMarketsSeeder::class,
            MarketPairSeeder::class,
            MarketPairsSeeder::class,
            OrderStatusesSeeder::class,
            PaymentMethodSeeder::class,
            PlansTableSeeder::class,
            TradeTypeSeeder::class,
           
        ]);
    }
}
