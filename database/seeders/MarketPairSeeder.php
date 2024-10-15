<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarketPairSeeder extends Seeder
{
    public function run()
    {
        $market_id = 1; // The market id for crypto trading

        // List of crypto pairs with live icon URLs
        $pairs = [
            ['base_currency' => 'BTC', 'quote_currency' => 'USDT', 'price' => 30000.00, 'icon' => 'https://cryptoicons.org/api/icon/btc/32', 'description' => 'Bitcoin paired with US Dollar Tether'],
            ['base_currency' => 'ETH', 'quote_currency' => 'USDT', 'price' => 2000.00, 'icon' => 'https://cryptoicons.org/api/icon/eth/32', 'description' => 'Ethereum paired with US Dollar Tether'],
            ['base_currency' => 'XRP', 'quote_currency' => 'USDT', 'price' => 0.75, 'icon' => 'https://cryptoicons.org/api/icon/xrp/32', 'description' => 'XRP paired with US Dollar Tether'],
            ['base_currency' => 'LTC', 'quote_currency' => 'USDT', 'price' => 150.00, 'icon' => 'https://cryptoicons.org/api/icon/ltc/32', 'description' => 'Litecoin paired with US Dollar Tether'],
            ['base_currency' => 'ADA', 'quote_currency' => 'USDT', 'price' => 1.50, 'icon' => 'https://cryptoicons.org/api/icon/ada/32', 'description' => 'Cardano paired with US Dollar Tether'],
            ['base_currency' => 'BNB', 'quote_currency' => 'USDT', 'price' => 400.00, 'icon' => 'https://cryptoicons.org/api/icon/bnb/32', 'description' => 'Binance Coin paired with US Dollar Tether'],
            ['base_currency' => 'DOT', 'quote_currency' => 'USDT', 'price' => 30.00, 'icon' => 'https://cryptoicons.org/api/icon/dot/32', 'description' => 'Polkadot paired with US Dollar Tether'],
            ['base_currency' => 'SOL', 'quote_currency' => 'USDT', 'price' => 50.00, 'icon' => 'https://cryptoicons.org/api/icon/sol/32', 'description' => 'Solana paired with US Dollar Tether'],
            ['base_currency' => 'DOGE', 'quote_currency' => 'USDT', 'price' => 0.05, 'icon' => 'https://cryptoicons.org/api/icon/doge/32', 'description' => 'Dogecoin paired with US Dollar Tether'],
            ['base_currency' => 'MATIC', 'quote_currency' => 'USDT', 'price' => 1.00, 'icon' => 'https://cryptoicons.org/api/icon/matic/32', 'description' => 'Polygon paired with US Dollar Tether'],
        ];

        // Insert pairs into the market_pairs table for market_id = 1
        foreach ($pairs as $pair) {
            DB::table('market_pairs')->insert([
                'market_id' => $market_id,
                'base_currency' => $pair['base_currency'],
                'quote_currency' => $pair['quote_currency'],
                'price' => $pair['price'],
                'status' => 'active', // Default to active
                'icon' => $pair['icon'],
                'description' => $pair['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo "Market pairs seeded successfully!";
    }
}
