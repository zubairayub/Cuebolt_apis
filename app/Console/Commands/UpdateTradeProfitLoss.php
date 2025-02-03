<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Trade;

class UpdateTradeProfitLoss extends Command
{
    protected $signature = 'trades:update-profit-loss';
    protected $description = 'Update trade profit/loss from live market data';

    public function handle()
    {
        $trades = Trade::where('status', '1')->get();

        foreach ($trades as $trade) {
            try {
                // Fetch live market data from Binance
                $symbol = strtoupper(str_replace('/', '', $trade->marketPair->symbol)); // Remove "/"
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

                        Log::info("Updated profit/loss for Trade ID {$trade->id}: {$profitLoss}%");
                    }
                } else {
                    Log::error("Failed to fetch market data for {$symbol}: " . $response->body());
                }
            } catch (\Exception $e) {
                Log::error("Error updating profit/loss for Trade ID: {$trade->id} - " . $e->getMessage());
            }
        }

        $this->info('Trade profit/loss updated successfully.');
    }
}
