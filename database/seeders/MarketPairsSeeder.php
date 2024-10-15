<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarketPairsSeeder extends Seeder
{
    public function run()
    {
        // Define market pairs and their related data, including symbols
        $pairs = [
            ['market_id' => 1, 'base_currency' => 'BTC', 'quote_currency' => 'USDT', 'symbol' => 'BTC/USDT', 'price' => 50000.00000000, 'status' => 'active', 'icon' => 'https://cryptoicons.org/api/icon/btc/50', 'description' => 'Bitcoin to US Dollar Tether'],
            ['market_id' => 1, 'base_currency' => 'ETH', 'quote_currency' => 'USDT', 'symbol' => 'ETH/USDT', 'price' => 4000.00000000, 'status' => 'active', 'icon' => 'https://cryptoicons.org/api/icon/eth/50', 'description' => 'Ethereum to US Dollar Tether'],
            ['market_id' => 1, 'base_currency' => 'ADA', 'quote_currency' => 'USDT', 'symbol' => 'ADA/USDT', 'price' => 2.50000000, 'status' => 'active', 'icon' => 'https://cryptoicons.org/api/icon/ada/50', 'description' => 'Cardano to US Dollar Tether'],
            ['market_id' => 2, 'base_currency' => 'AAPL', 'quote_currency' => 'TSLA', 'symbol' => 'AAPL/TSLA', 'price' => 145.00000000, 'status' => 'active', 'icon' => 'https://iconarchive.com/download/i107171/google/noto-emoji-animals-nature/apple.ico', 'description' => 'Apple and Tesla stock pair'],
            ['market_id' => 2, 'base_currency' => 'GOOG', 'quote_currency' => 'MSFT', 'symbol' => 'GOOG/MSFT', 'price' => 1500.00000000, 'status' => 'active', 'icon' => 'https://iconarchive.com/download/i107172/google/noto-emoji-animals-nature/google.ico', 'description' => 'Google and Microsoft stock pair'],
            ['market_id' => 3, 'base_currency' => 'EUR', 'quote_currency' => 'USD', 'symbol' => 'EUR/USD', 'price' => 1.18000000, 'status' => 'active', 'icon' => 'https://cryptoicons.org/api/icon/eur/50', 'description' => 'Euro to US Dollar pair'],
            ['market_id' => 3, 'base_currency' => 'GBP', 'quote_currency' => 'USD', 'symbol' => 'GBP/USD', 'price' => 1.38000000, 'status' => 'active', 'icon' => 'https://cryptoicons.org/api/icon/gbp/50', 'description' => 'British Pound to US Dollar'],
            ['market_id' => 4, 'base_currency' => 'GOLD', 'quote_currency' => 'OIL', 'symbol' => 'GOLD/OIL', 'price' => 1800.00000000, 'status' => 'inactive', 'icon' => 'https://iconarchive.com/download/i107176/google/noto-emoji-animals-nature/gold.ico', 'description' => 'Gold to Oil commodities trading'],
            ['market_id' => 5, 'base_currency' => 'BND', 'quote_currency' => 'IRX', 'symbol' => 'BND/IRX', 'price' => 100.00000000, 'status' => 'inactive', 'icon' => 'https://iconarchive.com/download/i107175/google/noto-emoji-animals-nature/bonds.ico', 'description' => 'Bond and Interest Rate trading'],
            ['market_id' => 6, 'base_currency' => 'SPX', 'quote_currency' => 'SPY', 'symbol' => 'SPX/SPY', 'price' => 4100.00000000, 'status' => 'active', 'icon' => 'https://cryptoicons.org/api/icon/spx/50', 'description' => 'S&P 500 options pair'],
            ['market_id' => 7, 'base_currency' => 'CL', 'quote_currency' => 'NQ', 'symbol' => 'CL/NQ', 'price' => 100.00000000, 'status' => 'inactive', 'icon' => 'https://iconarchive.com/download/i107174/google/noto-emoji-animals-nature/futures.ico', 'description' => 'Crude Oil vs Nasdaq Futures'],
            ['market_id' => 8, 'base_currency' => 'SPY', 'quote_currency' => 'QQQ', 'symbol' => 'SPY/QQQ', 'price' => 400.00000000, 'status' => 'active', 'icon' => 'https://cryptoicons.org/api/icon/spy/50', 'description' => 'SPY and QQQ ETF trading'],
            ['market_id' => 9, 'base_currency' => 'BTC', 'quote_currency' => 'DOGE', 'symbol' => 'BTC/DOGE', 'price' => 0.20, 'status' => 'active', 'icon' => 'https://cryptoicons.org/api/icon/doge/50', 'description' => 'Bitcoin to Dogecoin derivative'],
        ];

        // Insert the market pairs data into the `market_pairs` table
        foreach ($pairs as $pair) {
            DB::table('market_pairs')->insert([
                'market_id' => $pair['market_id'],
                'base_currency' => $pair['base_currency'],
                'quote_currency' => $pair['quote_currency'],
                'symbol' => $pair['symbol'],
                'price' => $pair['price'],
                'status' => $pair['status'],
                'icon' => $pair['icon'],
                'description' => $pair['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo "Market pairs seeded successfully!";
    }
}
