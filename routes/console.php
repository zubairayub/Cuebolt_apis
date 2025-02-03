<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\MonitorMarketPairsWebSocket;
use Illuminate\Support\Facades\Schedule;
use App\Models\Trade;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();




Artisan::command('monitor:market-pairs-websocket', function () {
    // Instantiate and run the command logic
    app(MonitorMarketPairsWebSocket::class)->handle();
});


//below if for cronjob
// * * * * * php /path/to/your/project/artisan schedule:run >> /dev/null 2>&1

// Schedule::command('monitor:market-pairs-websocket')
//     ->hourly()
//     ->withoutOverlapping();

Schedule::call(function () {
    // Fetch all active trades
    $trades = Trade::where('status', 'active')->get();

    foreach ($trades as $trade) {
        try {
            // Fetch live market data from Binance
            $symbol = strtoupper($trade->marketPair->symbol); // Ensure correct format (e.g., BTCUSDT)
            $symbol = strtolower(str_replace('/', '', $symbol));
            $response = Http::get("https://api.binance.com/api/v3/ticker/price", [
                'symbol' => $symbol,
            ]);

            if ($response->successful()) {
                $liveMarketData = $response->json();
                
                if (isset($liveMarketData['price'])) {
                    $currentPrice = (float) $liveMarketData['price'];
                    $entryPrice = (float) $trade->entry_price;

                    // Calculate profit/loss percentage
                    $profitLoss = (($currentPrice - $entryPrice) / $entryPrice) * 100;

                    // Update the trade record
                    $trade->update(['profit_loss' => $profitLoss]);
                }
            } else {
                Log::error("Failed to fetch market data for {$symbol}: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Error updating profit/loss for Trade ID: {$trade->id} - " . $e->getMessage());
        }
    }
})->everyMinute();

