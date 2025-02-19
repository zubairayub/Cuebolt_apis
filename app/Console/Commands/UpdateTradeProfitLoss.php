<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Trade;
use App\Models\Package;
use App\Models\SignalPerformance;


class UpdateTradeProfitLoss extends Command
{
    protected $signature = 'trades:update-profit-loss';
    protected $description = 'Update trade profit/loss from live market data';

    // public function handle()
    // {
    //     $trades = Trade::where('status', '1')->get();


    //     // Prepare an array of symbols for all trades (including duplicates)
    //     $symbols = [];
    //     $tradeSymbols = [];

    //     foreach ($trades as $trade) {
    //         $symbol = strtoupper(str_replace('/', '', $trade->marketPair->base_currency)); // Convert to uppercase and remove "/"
    //         $symbols[] = $symbol;  // Collect all symbols (including duplicates)
    //         $tradeSymbols[] = $trade; // Store the corresponding trades
    //     }

    //     // Remove duplicate symbols for the API request
    //     $uniqueSymbols = array_unique($symbols); // Only unique symbols for the API call

    //     // Define CoinMarketCap API Key
    //     $apiKey = '1b960532-df19-4600-861d-383dd5514ad1';

    //     // Function to fetch market data in chunks of 30 symbols
    //     function fetchMarketDataInChunks($symbols, $apiKey)
    //     {
    //         $chunkSize = 30; // CoinMarketCap allows up to 30 symbols per request
    //         $symbolChunks = array_chunk($symbols, $chunkSize); // Split symbols into chunks of 30
    //         $allData = [];

    //         foreach ($symbolChunks as $chunk) {
    //             // Join the chunk into a comma-separated string
    //             $symbolsString = implode(',', $chunk);

    //             try {
    //                 // Fetch data from CoinMarketCap API
    //                 $response = Http::withHeaders([
    //                     'X-CMC_PRO_API_KEY' => $apiKey,  // Pass the API Key in the header
    //                     'Accept' => 'application/json',  // Set the response format to JSON
    //                 ])->get("https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest", [
    //                             'symbol' => $symbolsString,    // Pass the symbols as a comma-separated string
    //                             'convert' => 'USD',            // Convert to USD (or any other currency)
    //                         ]);

    //                 // Check if the response is successful
    //                 if ($response->successful()) {
    //                     $data = $response->json();
    //                     $allData = array_merge($allData, $data['data']);
    //                 } else {
    //                     Log::error("Failed to fetch market data for symbols: " . $response->body());
    //                 }
    //             } catch (\Exception $e) {
    //                 Log::error("Error fetching market data: " . $e->getMessage());
    //             }
    //         }

    //         return $allData;
    //     }

    //     try {
    //         // Fetch all market data in chunks
    //         $marketData = fetchMarketDataInChunks($uniqueSymbols, $apiKey);

    //         // Loop through all the trades and update the profit/loss
    //         foreach ($tradeSymbols as $trade) {
    //             $symbol = strtoupper(str_replace('/', '', $trade->marketPair->base_currency)); // Same symbol as before

    //             // Check if the symbol data exists in the response
    //             if (!empty($marketData)) {
    //                 // Find the relevant data for the current symbol
    //                 $coinData = collect($marketData)->firstWhere('symbol', $symbol);

    //                 if ($coinData) {
    //                     // Get the current price from CoinMarketCap response
    //                     $currentPrice = $coinData['quote']['USD']['price'];
    //                     $entryPrice = (float) $trade->entry_price;

    //                     // Calculate profit/loss percentage
    //                     $profitLoss = (($currentPrice - $entryPrice) / $entryPrice) * 100;

    //                     // Update the trade record with the calculated profit/loss
    //                     $trade->update(['profit_loss' => $profitLoss]);

    //                     Log::info("Updated profit/loss for Trade ID {$trade->id}: {$profitLoss}%");
    //                 } else {
    //                     Log::error("Symbol {$symbol} not found in the CoinMarketCap response.");
    //                 }
    //             }
    //         }
    //     } catch (\Exception $e) {
    //         Log::error("Error updating profit/loss for trades: " . $e->getMessage());
    //     }





    //     $this->info('Trade profit/loss updated successfully.');
    // }




    //working latest is below
    public function handle()
    {
        $trades = Trade::where('status', '1')->get();

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
        function fetchMarketDataInChunks($symbols, $apiKey)
        {
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
                        // $currentPrice = '102461';
                        $entryPrice = (float) $trade->entry_price;
                        $takeProfit = (float) $trade->take_profit;
                        $stopLoss = (float) $trade->stop_loss;
                        $tradetype = $trade->tradeType->name;



                        // Calculate profit/loss percentage
                        $profitLoss = (($currentPrice - $entryPrice) / $entryPrice) * 100;

                        // Calculate Risk-Reward Ratio (RRR)
                        $rrr = ($takeProfit - $entryPrice) / ($entryPrice - $stopLoss);

                        if (is_null($trade->profit_loss) || $trade->profit_loss === '') {
                            Log::channel('trades_logs')->info("calcution Trade ID {$trade->id}: {$profitLoss}%: Price Live {$currentPrice} ");
                            if ($tradetype === 'BUY') {
                                // Condition for "buy" trade type
                                if ($currentPrice <= $entryPrice) {
                                    $trade->update([
                                        'profit_loss' => $profitLoss,
                                        'rrr' => $rrr, // Ensure you have an 'rrr' column in your trades table
                                        'notes' => $symbol . ' Long Trade Active',
                                    ]);
                                    Log::channel('trades_logs')->info("buy zone  Trade ID {$trade->id}: {$profitLoss}% ");
                                }
                            } else {
                                // Condition for "sell" or any other trade type
                                if ($currentPrice >= $entryPrice) {
                                    $trade->update([
                                        'profit_loss' => $profitLoss,
                                        'rrr' => $rrr, // Ensure you have an 'rrr' column in your trades table
                                        'notes' => $symbol . ' Short Trade Active',
                                    ]);
                                    Log::channel('trades_logs')->info("sell zone Trade ID {$trade->id}: {$profitLoss}% ");
                                }
                            }
                        }else{

                            if ($tradetype === 'BUY') {
                                // Condition for "buy" trade type
                                if ($currentPrice < $stopLoss) {
                                    $trade->update([
                                        'profit_loss' => $profitLoss,
                                        'rrr' => $rrr, // Ensure you have an 'rrr' column in your trades table
                                        'notes' => $symbol . ' Sl hit buy trade',
                                    ]);
                                    Log::channel('trades_logs')->info(" Sl hit buy trade Updated profit/loss for Trade ID {$trade->id}: {$profitLoss}%");
                                }
                                if ($currentPrice > $takeProfit) {
                                    $trade->update([
                                        'profit_loss' => $profitLoss,
                                        'rrr' => $rrr, // Ensure you have an 'rrr' column in your trades table
                                        'notes' => $symbol . ' TP hit buy trade',
                                    ]);
                                    Log::channel('trades_logs')->info(" TP hit buy trade Updated profit/loss for Trade ID {$trade->id}: {$profitLoss}%");
                                }
                            } else {
                                // Condition for "sell" or any other trade type
                                if ($currentPrice > $stopLoss) {
                                    $trade->update([
                                        'profit_loss' => $profitLoss,
                                        'rrr' => $rrr, // Ensure you have an 'rrr' column in your trades table
                                        'notes' => $symbol . ' sl hit short trade',
                                    ]);
                                    Log::channel('trades_logs')->info(" sl hit short trade Updated profit/loss for Trade ID {$trade->id}: {$profitLoss}%");
                                }
                                if ($currentPrice < $takeProfit) {
                                    $trade->update([
                                        'profit_loss' => $profitLoss,
                                        'rrr' => $rrr, // Ensure you have an 'rrr' column in your trades table
                                        'notes' => $symbol . ' tp hit short trade',
                                    ]);
                                    Log::channel('trades_logs')->info(" tp hit short trade Updated profit/loss for Trade ID {$trade->id}: {$profitLoss}%");
                                }
                            }

                        }
                        // if ((is_null($trade->profit_loss) || $trade->profit_loss === '') && ($currentPrice >= $takeProfit || $currentPrice <= $stopLoss)) {
                        //     // Update the trade record with profit/loss and RRR
                        //     $trade->update([
                        //         'profit_loss' => $profitLoss,
                        //         'rrr' => $rrr, // Ensure you have an 'rrr' column in your trades table
                        //     ]);
                        //     Log::channel('trades_logs')->info("Updated profit/loss for Trade ID {$trade->id}: {$profitLoss}%");
                        // }
                        // Update the corresponding SignalPerformance record
                        $signalPerformance = SignalPerformance::where('signal_id', $trade->id)->first();
                        if ($signalPerformance) {
                            $entryPrice_performance = (float) $signalPerformance->entry_price;
                            $takeProfit_performance = (float) $signalPerformance->take_profit;
                            $stopLoss_performance = (float) $signalPerformance->stop_loss;
                            $tradetype = $trade->tradeType->name;

                            // Calculate profit/loss percentage
                            $profitLoss_performance = (($currentPrice - $entryPrice_performance) / $entryPrice_performance) * 100;
                            // if ((is_null($signalPerformance->profit_loss) || $signalPerformance->profit_loss === '') && ($currentPrice >= $takeProfit_performance || $currentPrice <= $stopLoss_performance)) {
                            //     $signalPerformance->update([
                            //         'current_price' => $currentPrice,
                            //         'profit_loss' => $profitLoss_performance,
                            //     ]);
                            //     Log::channel('trades_logs')->info("Updated SignalPerformance for Signal ID {$trade->id}: Current Price: {$currentPrice}, Profit/Loss: {$profitLoss}%");
                            // }

                            if (is_null($signalPerformance->profit_loss) || $signalPerformance->profit_loss === '') {
                                if ($tradetype === 'BUY') {
                                    // Condition for "buy" trade type
                                    if ($currentPrice <= $entryPrice) {
                                        $signalPerformance->update([
                                            'current_price' => $currentPrice,
                                            'profit_loss' => $profitLoss_performance,
                                        ]);
                                        Log::channel('trades_logs')->info("Updated profit/loss for Trade ID Performance {$trade->id}: {$profitLoss}%");
                                    }
                                } else {
                                    // Condition for "sell" or any other trade type
                                    if ($currentPrice >= $entryPrice) {
                                        $signalPerformance->update([
                                            'current_price' => $currentPrice,
                                            'profit_loss' => $profitLoss_performance,
                                        ]);
                                        Log::channel('trades_logs')->info("Updated profit/loss for Trade ID Performance {$trade->id}: {$profitLoss}%");
                                    }
                                }
                            }
                        } else {
                            Log::channel('trades_logs')->error("SignalPerformance record not found for Signal ID {$trade->id}");
                        }


                    } else {
                        Log::channel('trades_logs')->error("Symbol {$symbol} not found in the CoinMarketCap response.");
                    }
                }
            }

            // Now update the package's win/loss percentage based on all trades of that package
            $packages = Package::all(); // Fetch all packages

            foreach ($packages as $package) {
                // Get all trades for this package
                $packageTrades = $package->trades; // Assuming relationship between Package and Trade is defined

                // Count total trades, winning trades, and losing trades
                $totalTrades = $packageTrades->count();
                $winningTrades = $packageTrades->where('profit_loss', '>=', 0)->count();
                $losingTrades = $totalTrades - $winningTrades; // The rest are losing trades

                // Calculate the winning and losing percentages
                $winPercentage = ($totalTrades > 0) ? ($winningTrades / $totalTrades) * 100 : 0;
                $lossPercentage = ($totalTrades > 0) ? ($losingTrades / $totalTrades) * 100 : 0;

                // Calculate the average of win_percentage and loss_percentage
                $avgProfitLossPercentage = ($winPercentage + $lossPercentage) / 2;

                // Initialize variables to accumulate RRR values
                $totalRRR = 0;

                // Loop through each trade and calculate the RRR
                foreach ($packageTrades as $trade) {
                    $entryPrice = (float) $trade->entry_price;
                    $stopLossPrice = (float) $trade->stop_loss;
                    $takeProfitPrice = (float) $trade->take_profit;

                    // Calculate Risk and Reward for the trade
                    $risk = $entryPrice - $stopLossPrice; // Risk = Entry price - Stop loss price
                    $reward = $takeProfitPrice - $entryPrice; // Reward = Take profit price - Entry price

                    // Calculate RRR (Reward-to-Risk Ratio)
                    if ($risk > 0) {
                        $rrr = $reward / $risk; // RRR = Reward / Risk
                        $totalRRR += $rrr;
                    }
                }

                // Calculate the average RRR for the package
                $avgRRR = ($totalTrades > 0) ? $totalRRR / $totalTrades : 0;

                // Update the package with win/loss percentages, the calculated average profit/loss percentage, and average RRR
                $package->update([
                    'win_percentage' => $winPercentage,
                    'loss_percentage' => $lossPercentage,
                    'profit_loss_percentage' => $avgProfitLossPercentage,  // Store the average in the profit_loss_percentage column
                    'achieved_rrr' => $avgRRR,  // Assuming there's a column for avg_rrr in the packages table
                ]);

                Log::channel('trades_logs')->info("Updated win/loss percentages and average RRR for Package ID {$package->id}: {$winPercentage}% win, {$lossPercentage}% loss, Avg RRR: {$avgRRR}");
            }

        } catch (\Exception $e) {
            Log::channel('trades_logs')->error("Error updating profit/loss for trades: " . $e->getMessage());
        }

        Log::channel('trades_logs')->info('Trade profit/loss and package win/loss percentages updated successfully.');
    }



    // public function handle()
    // {
    //     // Fetch all active trades
    //     $trades = Trade::where('status', '1')->get();

    //     // Prepare an array of symbols for trades that are close to stop loss or take profit
    //     $symbols = [];
    //     $tradeSymbols = [];

    //     foreach ($trades as $trade) {
    //         $symbol = strtoupper(str_replace('/', '', $trade->marketPair->base_currency)); // Convert to uppercase and remove "/"
    //         $symbols[] = $symbol;
    //         // Check if the trade is close to stop loss or take profit
    //         $entryPrice = (float) $trade->entry_price;
    //         $takeProfit = (float) $trade->take_profit;
    //         $stopLoss = (float) $trade->stop_loss;

    //         // Define a threshold (e.g., 5%) to check if the price is close to stop loss or take profit
    //         //$threshold = 0.05; // 5%
    //         // $currentPrice = $this->getCurrentPrice($symbol); // Fetch the current price for the symbol

    //         // if ($currentPrice && ($currentPrice >= $takeProfit * (1 - $threshold) || $currentPrice <= $stopLoss * (1 + $threshold))) {
    //         //     $symbols[] = $symbol;  // Collect symbols for trades close to stop loss or take profit
    //         //     $tradeSymbols[] = $trade; // Store the corresponding trades
    //         // }
    //     }

    //     // Remove duplicate symbols for the API request
    //     $uniqueSymbols = array_unique($symbols); // Only unique symbols for the API call

    //     // Define CoinMarketCap API Key
    //     $apiKey = '1b960532-df19-4600-861d-383dd5514ad1';

    //     // Fetch market data for the filtered symbols
    //     $marketData = $this->fetchMarketDataInChunks($uniqueSymbols, $apiKey);

    //     // Loop through the filtered trades and update the profit/loss
    //     foreach ($tradeSymbols as $trade) {
    //         $symbol = strtoupper(str_replace('/', '', $trade->marketPair->base_currency)); // Same symbol as before

    //         // Check if the symbol data exists in the response
    //         if (!empty($marketData)) {
    //             // Find the relevant data for the current symbol
    //             $coinData = collect($marketData)->firstWhere('symbol', $symbol);

    //             if ($coinData) {
    //                 // Get the current price from CoinMarketCap response
    //                 //$currentPrice = $coinData['quote']['USD']['price'];
    //                 $currentPrice = '102461';
    //                 $entryPrice = (float) $trade->entry_price;
    //                 $takeProfit = (float) $trade->take_profit;
    //                 $stopLoss = (float) $trade->stop_loss;

    //                 // Calculate profit/loss percentage
    //                 $profitLoss = (($currentPrice - $entryPrice) / $entryPrice) * 100;

    //                 // Calculate Risk-Reward Ratio (RRR)
    //                 $rrr = ($takeProfit - $entryPrice) / ($entryPrice - $stopLoss);

    //                 if ((is_null($trade->profit_loss) || $trade->profit_loss === '') && ($currentPrice >= $takeProfit || $currentPrice <= $stopLoss)) {
    //                     // Update the trade record with profit/loss and RRR
    //                     $trade->update([
    //                         'profit_loss' => $profitLoss,
    //                         'rrr' => $rrr, // Ensure you have an 'rrr' column in your trades table
    //                     ]);
    //                 }
    //                 // Update the corresponding SignalPerformance record
    //                 $signalPerformance = SignalPerformance::where('signal_id', $trade->id)->first();
    //                 if ($signalPerformance) {
    //                     $entryPrice_performance = (float) $signalPerformance->entry_price;
    //                     $takeProfit_performance = (float) $signalPerformance->take_profit;
    //                     $stopLoss_performance = (float) $signalPerformance->stop_loss;

    //                     // Calculate profit/loss percentage
    //                     $profitLoss_performance = (($currentPrice - $entryPrice_performance) / $entryPrice_performance) * 100;
    //                     if ((is_null($signalPerformance->profit_loss) || $signalPerformance->profit_loss === '') && ($currentPrice >= $takeProfit_performance || $currentPrice <= $stopLoss_performance)) {
    //                         $signalPerformance->update([
    //                             'current_price' => $currentPrice,
    //                             'profit_loss' => $profitLoss_performance,
    //                         ]);
    //                         Log::channel('trades_logs')->info("Updated SignalPerformance for Signal ID {$trade->id}: Current Price: {$currentPrice}, Profit/Loss: {$profitLoss}%");
    //                     }
    //                 } else {
    //                     Log::channel('trades_logs')->error("SignalPerformance record not found for Signal ID {$trade->id}");
    //                 }

    //                 Log::channel('trades_logs')->info("Updated profit/loss for Trade ID {$trade->id}: {$profitLoss}%");
    //             } else {
    //                 Log::channel('trades_logs')->error("Symbol {$symbol} not found in the CoinMarketCap response.");
    //             }
    //         }
    //     }

    //     Log::channel('trades_logs')->info('Trade profit/loss updated successfully.');
    // }

    // Helper function to fetch the current price for a symbol
    // private function getCurrentPrice($symbol)
    // {
    //     // You can use a local cache or a simple API call to get the current price
    //     // For example, using a cached value or a lightweight API
    //     // This is a placeholder function, replace it with your actual implementation
    //     return Cache::remember("price_{$symbol}", 1, function () use ($symbol) {
    //         // Fetch the current price from a lightweight API or database
    //         // Example: return SomeLightweightAPI::getPrice($symbol);
    //         return null;
    //     });
    // }

    // Function to fetch market data in chunks of 30 symbols
    private function fetchMarketDataInChunks($symbols, $apiKey)
    {
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
                    Log::channel('trades_logs')->error("Failed to fetch market data for symbols: " . $response->body());
                }
            } catch (\Exception $e) {
                Log::channel('trades_logs')->error("Error fetching market data: " . $e->getMessage());
            }
        }

        return $allData;
    }
}
