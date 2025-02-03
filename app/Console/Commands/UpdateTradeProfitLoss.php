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

        // foreach ($trades as $trade) {

        //     try {
        //         // Fetch live market data from Binance
        //         $symbol = strtoupper(str_replace('/', '', $trade->marketPair->symbol)); // Remove "/"
        //         $response = Http::get("https://api.binance.com/api/v3/ticker/price", [
        //             'symbol' => $symbol,
        //         ]);


        //         if ($response->successful()) {
        //             $liveMarketData = $response->json();

        //             if (isset($liveMarketData['price'])) {
        //                 $currentPrice = (float) $liveMarketData['price'];
        //                 $entryPrice = (float) $trade->entry_price;

        //                 // Calculate profit/loss percentage
        //                 $profitLoss = (($currentPrice - $entryPrice) / $entryPrice) * 100;

        //                 // Update the trade record
        //                 $trade->update(['profit_loss' => $profitLoss]);

        //                 Log::info("Updated profit/loss for Trade ID {$trade->id}: {$profitLoss}%");
        //             }
        //         } else {
        //             Log::error("Failed to fetch market data for {$symbol}: " . $response->body());
        //         }
        //     } catch (\Exception $e) {
        //         Log::error("Error updating profit/loss for Trade ID: {$trade->id} - " . $e->getMessage());
        //     }
        // }


       // Prepare an array of symbols for all trades (including duplicates)
       // Prepare an array of symbols for all trades (including duplicates)
$symbols = [];
$tradeSymbols = [];

foreach ($trades as $trade) {
    $symbol = strtoupper(str_replace('/', '', $trade->marketPair->base_currency)); // Convert to uppercase and remove "/"
    $symbols[] = $symbol;  // Collect all symbols (including duplicates)
    $tradeSymbols[] = $trade; // Store the corresponding trades
}

// Remove duplicate symbols for the API request
$uniqueSymbols = array_unique($symbols); // Only unique symbols for the API call

// Define CoinMarketCap API Key
$apiKey = '1b960532-df19-4600-861d-383dd5514ad1';

// Function to fetch market data in chunks of 30 symbols
function fetchMarketDataInChunks($symbols, $apiKey) {
    $chunkSize = 30; // CoinMarketCap allows up to 30 symbols per request
    $symbolChunks = array_chunk($symbols, $chunkSize); // Split symbols into chunks of 30
    $allData = [];

    foreach ($symbolChunks as $chunk) {
        // Join the chunk into a comma-separated string
        $symbolsString = implode(',', $chunk);

        try {
            // Fetch data from CoinMarketCap API
            $response = Http::withHeaders([
                'X-CMC_PRO_API_KEY' => $apiKey,  // Pass the API Key in the header
                'Accept' => 'application/json',  // Set the response format to JSON
            ])->get("https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest", [
                'symbol' => $symbolsString,    // Pass the symbols as a comma-separated string
                'convert' => 'USD',            // Convert to USD (or any other currency)
            ]);

            // Check if the response is successful
            if ($response->successful()) {
                $data = $response->json();
                $allData = array_merge($allData, $data['data']);
            } else {
                Log::error("Failed to fetch market data for symbols: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Error fetching market data: " . $e->getMessage());
        }
    }

    return $allData;
}

try {
    // Fetch all market data in chunks
    $marketData = fetchMarketDataInChunks($uniqueSymbols, $apiKey);

    // Loop through all the trades and update the profit/loss
    foreach ($tradeSymbols as $trade) {
        $symbol = strtoupper(str_replace('/', '', $trade->marketPair->base_currency)); // Same symbol as before

        // Check if the symbol data exists in the response
        if (!empty($marketData)) {
            // Find the relevant data for the current symbol
            $coinData = collect($marketData)->firstWhere('symbol', $symbol);

            if ($coinData) {
                // Get the current price from CoinMarketCap response
                $currentPrice = $coinData['quote']['USD']['price'];
                $entryPrice = (float) $trade->entry_price;

                // Calculate profit/loss percentage
                $profitLoss = (($currentPrice - $entryPrice) / $entryPrice) * 100;

                // Update the trade record with the calculated profit/loss
                $trade->update(['profit_loss' => $profitLoss]);

                Log::info("Updated profit/loss for Trade ID {$trade->id}: {$profitLoss}%");
            } else {
                Log::error("Symbol {$symbol} not found in the CoinMarketCap response.");
            }
        }
    }
} catch (\Exception $e) {
    Log::error("Error updating profit/loss for trades: " . $e->getMessage());
}





        $this->info('Trade profit/loss updated successfully.');
    }
}
