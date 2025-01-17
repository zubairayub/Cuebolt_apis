<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use App\Models\SignalPerformance;
use App\Models\MarketPair;
use WebSocket\Client as WebSocketClient;

class ProcessWebSocketJob
{
    protected $pairs;

    public function __construct($pairs)
    {
        $this->pairs = $pairs; // Multiple market pairs as input
    }

    public function handle(): void
    {
        // WebSocket base URL
        $websocketUrl = "wss://stream.binance.com:9443/ws/";

        // Prepare the array of pairs
        $pairStreams = [];
        foreach ($this->pairs as $pair) {
            // Format each symbol for WebSocket (remove '/' and convert to lowercase)
            $formattedPair = strtolower(str_replace('/', '', $pair));
            $pairStreams[] = $formattedPair . '@trade';
        }

        // Join the pair streams with a "/" separator to subscribe to multiple streams
        $url = $websocketUrl . implode('/', $pairStreams);

        Log::info("Connecting to WebSocket URL: {$url}");

        try {
            // Initialize WebSocket client
            $ws = new WebSocketClient($url, ['timeout' => 10]);

            while (true) {
                try {
                    $message = $ws->receive();
                    $data = json_decode($message, true);

                    if ($data && isset($data['p'])) {
                        $currentPrice = $data['p'];
                        $pair = strtoupper($data['s']); // The symbol of the pair

                        $formattedPair = strtoupper(substr($pair, 0, 3)) . '/' . strtoupper(substr($pair, 3));

                      //  Log::info("Received price update for {$formattedPair}: $currentPrice");

                        // Fetch the market pair ID from the database
                        $marketPairId = $this->getMarketPairIdBySymbol($formattedPair);

                        // Fetch corresponding signals for the current pair only if market pair exists
                        if ($marketPairId) {
                            $signals = SignalPerformance::where('market_pair_id', $marketPairId)->get();

                            foreach ($signals as $signal) {
                                // Check if the signal status is not 'completed'
                                //if ($signal->status !== 'completed') {
                                    // Check if the current price reached the take profit or stop loss
                                    if ($currentPrice >= $signal->take_profit || $currentPrice <= $signal->stop_loss) {
                                        // Update the signal performance
                                        $this->updateSignalPerformance($signal, $currentPrice);
                                    }
                               // }
                            }
                        } else {
                            Log::warning("Market pair not found for {$formattedPair}");
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Error processing WebSocket message: " . $e->getMessage());
                }

                // Delay to avoid overwhelming the WebSocket server
                sleep(1); // Adjust the sleep time based on your needs (1 second delay)
            }
        } catch (\Exception $e) {
            Log::error("Error connecting to WebSocket: " . $e->getMessage());
        } finally {
            // Ensure the WebSocket connection is properly closed
            if (isset($ws)) {
                $ws->close();
                Log::info("WebSocket connection closed.");
            }
        }
    }

    private function updateSignalPerformance($signal, $currentPrice)
    {
        // Calculate profit/loss
        $profitLoss = $currentPrice - $signal->entry_price;

        // Update the database with new profit/loss and status
        $signal->update([
            'profit_loss' => $profitLoss,
            'current_price' => $currentPrice,
            'status' => 'completed',
        ]);

       // Log::info("Updated signal performance for {$signal->symbol} with price {$currentPrice}");
    }

    private function getMarketPairIdBySymbol(string $pair): ?int
    {
        // Fetch the market pair ID based on the symbol from the database
        $marketPair = MarketPair::where('symbol', $pair)->first();
        return $marketPair ? $marketPair->id : null;
    }
}
