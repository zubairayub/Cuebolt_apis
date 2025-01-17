<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MarketPair;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessWebSocketJob;

class MonitorMarketPairsWebSocket extends Command
{
    protected $signature = 'monitor:market-pairs-websocket';
    protected $description = 'Listen for price updates using WebSocket for market pairs and update signal performance when conditions hit';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Fetch market pairs from the database
        try {
            $marketPairs = MarketPair::where('market_id', 1)->get();
            if ($marketPairs->isEmpty()) {
                Log::warning("No market pairs found for the specified market_id.");
                return;
            }

            // Get the list of pairs to track
            $pairs = $marketPairs->pluck('symbol')->toArray();

            // Dispatch the job to process WebSocket for all pairs
            dispatch(new ProcessWebSocketJob($pairs));

            Log::info("WebSocket jobs dispatched for all market pairs.");
        } catch (\Exception $e) {
            Log::error("Error while fetching market pairs or dispatching WebSocket job: " . $e->getMessage());
        }
    }
}
