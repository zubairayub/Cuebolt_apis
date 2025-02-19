<?php

namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Package;
use App\Models\Trade;
use App\Models\Order;
use App\Models\SignalPerformance;
use App\Models\UserReview;
use App\Models\UserProfile;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TraderDashboardController extends Controller
{
    /**
     * Show the trader's dashboard.
     */
    public function showDashboard(Request $request, $username)
    {
        // Get the authenticated trader
         // Find the user by username
         $trader = User::where('username', $username)->first();

         // If the user does not exist, return an error response
         if (!$trader) {
             return response()->json(['error' => 'User not found'], 404);
         }



        // User Information Overview
        $profilePicture = optional($trader->profile)->profile_picture ?? asset('default-avatar.png');
        $name = $trader->username ?? 'Unknown Trader';

        // Use try-catch blocks for potential exceptions in helper methods
        try {
            $profile = $this->getTraderProfile($trader) ?? collect();
            $earnings = $this->getTotalEarnings($trader) ?? 0;
            $signalFollowers = $this->getFollowersCount($trader) ?? 0;
            $totalSignals = $this->getTotalAndActiveSignals($trader) ?? ['total' => 0, 'active' => 0];
            $successRate = $this->getSignalSuccessRate($trader) ?? 0;
            $signalsAndTopPerformer = $this->getSignalsAndTopPerformer($trader) ?? ['recent_signals' => collect(), 'top_performer' => collect()];
            $challengeProgress = $this->getChallengeProgress($trader) ?? collect();
            $rating = $this->getTraderRating($trader) ?? 0;
        } catch (\Exception $e) {
            // Log the error and assign safe fallback values
            \Log::error('Error in showDashboard: ' . $e->getMessage());
            $profile = collect();
            $earnings = 0;
            $signalFollowers = 0;
            $totalSignals = ['total' => 0, 'active' => 0];
            $successRate = 0;
            $signalsAndTopPerformer = ['recent_signals' => collect(), 'top_performer' => collect()];
            $challengeProgress = collect();
            $rating = 0;
        }

        // Ensure these variables are always iterable
        $topSignals = collect($signalsAndTopPerformer['recent_signals'] ?? []);
        $topPerformingSignals = collect($signalsAndTopPerformer['top_performer'] ?? []);

        // Handle empty cases safely
        if (!$topSignals instanceof \Illuminate\Support\Collection) {
            $topSignals = collect();
        }
        if (!$topPerformingSignals instanceof \Illuminate\Support\Collection) {
            $topPerformingSignals = collect();
        }

        // Get top packages with safe handling
        try {
            $topPackages = Package::where('status', 1)
                ->where('user_id', $trader->id)
                ->orderByDesc('win_percentage')
                ->get() ?? collect();
        } catch (\Exception $e) {
            \Log::error('Error fetching top packages: ' . $e->getMessage());
            $topPackages = collect();
        }

        // Return data to the Blade view
        return view('inner-pages.trader-dashboard', compact(
            'profilePicture',
            'name',
            'profile',
            'signalFollowers',
            'totalSignals',
            'successRate',
            'topSignals',
            'topPerformingSignals',
            'earnings',
            'challengeProgress',
            'rating',
            'topPackages'
        ));
    }





    // public function showDashboard(Request $request)
    // {
    //     try {
    //         // Get the authenticated trader
    //         $trader = Auth::user();

    //         // User Information Overview
    //         $profilePicture = $trader->profile->profile_picture ?? asset('default-avatar.png');
    //         $name = $trader->username;

    //         // Handle potential null values or empty data
    //         $profile = $this->getTraderProfile($trader) ?? [];  // Default to empty array if null
    //         $earnings = $this->getTotalEarnings($trader) ?? 0;  // Default to 0 if null
    //         $signalFollowers = $this->getFollowersCount($trader) ?? 0;  // Default to 0 if null
    //         $totalSignals = $this->getTotalAndActiveSignals($trader) ?? ['total' => 0, 'active' => 0];  // Default to empty array
    //         $successRate = $this->getSignalSuccessRate($trader) ?? 0;  // Default to 0 if null
    //         $signalsAndTopPerformer = $this->getSignalsAndTopPerformer($trader) ?? ['recent_signals' => [], 'top_performer' => []];  // Default to empty arrays
    //         $challengeProgress = $this->getChallengeProgress($trader) ?? 0;  // Default to 0 if null
    //         $rating = $this->getTraderRating($trader) ?? 0;  // Default to 0 if null

    //         // Get top packages, ensure it's always an array or collection
    //         $topPackages = Package::where('status', 1)
    //             ->where('user_id', $trader->id)  // If applicable, filter based on user's ownership
    //             ->orderByDesc('win_percentage')
    //             ->get();

    //         // Ensure that $topPackages is not null or empty
    //         if ($topPackages->isEmpty()) {
    //             $topPackages = collect();  // Use empty collection if no packages
    //         }

    //         // Return data to the Blade view
    //         return view('inner-pages.trader-dashboard', compact(
    //             'profilePicture',
    //             'name',
    //             'profile',
    //             'signalFollowers',
    //             'totalSignals',
    //             'successRate',
    //             'topSignals',
    //             'topPerformingSignals',
    //             'earnings',
    //             'challengeProgress',
    //             'rating',
    //             'topPackages'
    //         ));
    //     } catch (\Exception $e) {
    //         // Catch any unexpected errors and return a user-friendly error message
    //         // Log the error for further analysis
    //         //Log::error("Error loading trader dashboard: " . $e->getMessage());

    //         // Return a fallback view with an error message
    //         return view('inner-pages.trader-dashboard', [
    //             'error' => 'There was an issue loading your dashboard. Please try again later.'
    //         ]);
    //     }
    // }



    // Helper Methods

    // Get the trader's badge (can be based on performance, level, or rank)
    private function getTraderProfile($trader)
    {
        // Fetch the profile with related country, city, and badge data
        $profile = UserProfile::with(['country', 'city', 'badge'])
            ->where('user_id', $trader->id)
            ->first();

        return $profile;
    }

    // Get the total earnings from signal sales (in fiat and crypto)
    private function getTotalEarnings($trader)
    {
        // Get overall earnings
        $overallEarnings = Order::where('user_id', $trader->id)
            ->where('order_status_id', 2) // Assuming status 2 means "completed"
            ->sum('amount');

        // Get current month's earnings
        $currentMonthEarnings = Order::where('user_id', $trader->id)
            ->where('order_status_id', 2)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        // Get current year's earnings
        $currentYearEarnings = Order::where('user_id', $trader->id)
            ->where('order_status_id', 2)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        return [
            'overall' => $overallEarnings,
            'month' => $currentMonthEarnings,
            'year' => $currentYearEarnings,
        ];
    }

    // Get the total number of signals shared by the trader
    private function getTotalAndActiveSignals($trader)
    {
        try {
            // Base query for trades linked to the trader
            $baseQuery = Trade::whereIn('package_id', function ($query) use ($trader) {
                $query->select('id')
                    ->from('packages')
                    ->where('user_id', $trader->id);
            });

            // Total number of signals
            $totalSignals = (clone $baseQuery)->count();

            // Total number of active signals (status = 1)
            $activeSignals = (clone $baseQuery)->where('status', 1)->count();

            return [
                'total_signals' => $totalSignals,
                'active_signals' => $activeSignals,
            ];
        } catch (\Exception $e) {
            // Handle exceptions gracefully
            \Log::error("Error fetching signals for trader {$trader->id}: " . $e->getMessage());
            return [
                'total_signals' => 0,
                'active_signals' => 0,
            ]; // Return fallback values in case of error
        }
    }


    // Get the success rate of signals
    private function getSignalSuccessRate($trader)
    {
        try {
            // Get success and total signals in one query
            $result = Trade::selectRaw("
                SUM(CASE 
                    WHEN profit_loss >= take_profit OR profit_loss >= take_profit_2 THEN 1 
                    ELSE 0 
                END) as successful_signals,
                COUNT(*) as total_signals
            ")
                ->whereIn('package_id', function ($query) use ($trader) {
                    $query->select('id')
                        ->from('packages')
                        ->where('user_id', $trader->id);
                })
                ->first();

            $successRate = $result->total_signals > 0
                ? ($result->successful_signals / $result->total_signals) * 100
                : 0;

            return ['success_rate' => $successRate];

        } catch (\Exception $e) {
            \Log::error("Error calculating signal success rate for trader {$trader->id}: " . $e->getMessage());
            return response()->json(['error' => 'Unable to calculate signal success rate'], 500);
        }
    }

    // Get the trader's challenge progress
    private function getChallengeProgress($trader)
    {
        // Example: You can calculate the trader's challenge progress based on successful trades
        // $totalChallenges = Challenge::where('user_id', $trader->id)->count();
        // $completedChallenges = Challenge::where('user_id', $trader->id)->where('status', 'completed')->count();
        // return $totalChallenges > 0 ? ($completedChallenges / $totalChallenges) * 100 : 0;
    }

    // Get the trader's rating
    private function getTraderRating($trader)
    {
        // Example: Calculate average rating from ratings table
        $averageRating = UserReview::where('trader_id', $trader->id)->avg('rating'); // Assuming rating field exists in ratings table
        return round($averageRating, 1); // Round to 1 decimal place
    }

    private function getFollowersCount($trader)
    {
        try {
            // Fetch the count of followers directly using nested queries
            $followersCount = SignalPerformance::whereIn('signal_id', function ($query) use ($trader) {
                $query->select('id')
                    ->from('trades')
                    ->whereIn('package_id', function ($subQuery) use ($trader) {
                        $subQuery->select('id')
                            ->from('packages')
                            ->where('user_id', $trader->id);
                    });
            })->count();

            return $followersCount;
        } catch (\Exception $e) {
            // Handle exceptions gracefully
            \Log::error("Error fetching followers count for trader {$trader->id}: " . $e->getMessage());
            return 0; // Return 0 in case of error
        }

    }

    private function getSignalsAndTopPerformer($trader)
    {
        try {
            // Fetch 3 most recent signals
                $recentSignals = Trade::whereIn('package_id', function ($query) use ($trader) {
                    $query->select('id')
                        ->from('packages')
                        ->where('user_id', $trader->id);
                })
                ->orderByDesc('created_at')
                ->with(['package', 'marketPair', 'tradeType'])
                ->take(3) // Limit to avoid excessive queries
                ->get()
                ->map(function ($trade) {
                    // Avoid division by zero
                    $trade->percentageDifferencetp = ($trade->entry_price > 0)
                        ? (($trade->take_profit - $trade->entry_price) / $trade->entry_price) * 100
                        : 0;
            
                    $trade->percentageDifferencesl = ($trade->entry_price > 0)
                        ? (($trade->stop_loss - $trade->entry_price) / $trade->entry_price) * 100
                        : 0;
            
                    // Calculate Risk-Reward Ratio (RRR)
                    if ($trade->entry_price > 0 && ($trade->entry_price - $trade->stop_loss) > 0) {
                        $trade->prrr = ($trade->take_profit - $trade->entry_price) / ($trade->entry_price - $trade->stop_loss);
                    } else {
                        $trade->prrr = 0; // Default if invalid
                    }
            
                    return $trade;
                });
            

          
            // Fetch all trades for top performance evaluation
            $trades = Trade::whereIn('package_id', function ($query) use ($trader) {
                $query->select('id')
                    ->from('packages')
                    ->where('user_id', $trader->id);
            })
            ->orderByDesc('created_at')
            ->with(['package', 'marketPair', 'tradeType'])
            ->take(3) // Limit to avoid excessive queries
            ->get()
            ->map(function ($trade) {
                // Avoid division by zero
                $trade->percentageDifferencetp = ($trade->entry_price > 0)
                    ? (($trade->take_profit - $trade->entry_price) / $trade->entry_price) * 100
                    : 0;
        
                $trade->percentageDifferencesl = ($trade->entry_price > 0)
                    ? (($trade->stop_loss - $trade->entry_price) / $trade->entry_price) * 100
                    : 0;
        
                // Calculate Risk-Reward Ratio (RRR)
                if ($trade->entry_price > 0 && ($trade->entry_price - $trade->stop_loss) > 0) {
                    $trade->prrr = ($trade->take_profit - $trade->entry_price) / ($trade->entry_price - $trade->stop_loss);
                } else {
                    $trade->prrr = 0; // Default if invalid
                }
        
                return $trade;
            });


            foreach ($recentSignals as $trade) {
                $symbol = $trade->marketPair->symbol ?? null; // Assuming `market_pair` contains the symbol

                $entryPrice = $trade->entry_price;

                if ($symbol && $entryPrice) {
                    $currentPrice = $this->getLivePriceFromBinance($symbol);

                            // Add current price to the trade array
                            $trade['current_price'] = $currentPrice;
                            $recentSignalswithprice[] = $trade;

                    
                }
            }

            //dd($recentSignalswithprice);


            // Calculate top-performing signal
            $topPerformingSignal = $this->getTopPerformingSignal($trades);

            return [
                'recent_signals' => $recentSignalswithprice,
                'top_performer' => $topPerformingSignal,
            ];
        } catch (\Exception $e) {
            \Log::error("Error fetching signals and top performer for trader {$trader->id}: " . $e->getMessage());
            return [
                'recent_signals' => [],
                'top_performer' => null,
            ];
        }
    }

    private function getTopPerformingSignal($trades)
    {
        try {
            $topSignal = null;
            $maxPerformance = -INF; // Initialize with a very low value

            foreach ($trades as $trade) {
                $symbol = $trade->marketPair->symbol ?? null; // Assuming `market_pair` contains the symbol

                $entryPrice = $trade->entry_price;

                if ($symbol && $entryPrice) {
                    $currentPrice = $this->getLivePriceFromBinance($symbol);

                    if ($currentPrice) {
                        // Calculate performance as a percentage
                        $performance = (($currentPrice - $entryPrice) / $entryPrice) * 100;

                        if ($performance > $maxPerformance) {
                            $maxPerformance = $performance;
                            // Add current price to the trade array
                            $trade['current_price'] = $currentPrice;
                            $topSignal[] = $trade;

                        }
                    }
                }
            }

            return $topSignal;
        } catch (\Exception $e) {
            \Log::error("Error calculating top-performing signal: " . $e->getMessage());
            return null;
        }
    }


    private function getLivePriceFromBinance($symbol,$base_currency,$quote_currency)
    {
        // try {
        //     $binanceSymbol = str_replace('/', '', strtoupper($symbol));
        //     $apiUrl = "https://api.binance.com/api/v3/ticker/price?symbol=" . strtoupper($binanceSymbol);

        //     $response = file_get_contents($apiUrl);
        //     $data = json_decode($response, true);

        //     if (isset($data['price'])) {
        //         return (float) $data['price'];
        //     }
        // } catch (\Exception $e) {
        //     \Log::error("Error fetching live price for symbol {$symbol}: " . $e->getMessage());
        // }

        // return null; // Return null if unable to fetch price




         // Convert symbol for consistency
    $binanceSymbol = str_replace('/', '', strtoupper($symbol));
    
    // List of APIs to try
    $apiUrls = [
        // Binance
        "https://api.binance.com/api/v3/ticker/price?symbol=" . strtoupper($binanceSymbol),
        
        
        // CryptoCompare
        "https://min-api.cryptocompare.com/data/price?fsym=" . strtoupper($base_currency) . "&tsyms=".$quote_currency,
        
        // KuCoin
       // "https://api.kucoin.com/api/v1/market/orderbook/level1?symbol=" . strtoupper($symbol) . "-USDT",

        // Bitfinex
       // "https://api-pub.bitfinex.com/v2/tickers?symbols=t" . strtoupper($symbol) . "USDT",

        // Kraken
        "https://api.kraken.com/0/public/Ticker?pair=" . strtoupper($binanceSymbol) ,
        
        // Gemini
        "https://api.gemini.com/v1/pubticker/" . strtoupper($binanceSymbol),

        // Nomics
      //  "https://api.nomics.com/v1/currencies/ticker?key=YOUR_API_KEY&ids=" . strtolower($symbol) . "&convert=USD",

        // Messari
        "https://data.messari.io/api/v1/assets/" . strtolower($base_currency) . "/metrics/market-data"
    ];

    foreach ($apiUrls as $apiUrl) {
        try {
            $response = file_get_contents($apiUrl);
            $data = json_decode($response, true);

            // Check if price exists for this API and return it
            if (isset($data['price'])) {
                return (float) $data['price'];
            }
            
            // Special handling for APIs that may return data in different formats
            if (isset($data[0]['price'])) { // Bitfinex and others may return data in a list
                return (float) $data[0]['price'];
            }
            
            if (isset($data['ticker']['last'])) { // Kraken may return data in a nested format
                return (float) $data['ticker']['last'];
            }

            // Special handling for Nomics
            if (isset($data[0]['price'])) {
                return (float) $data[0]['price'];
            }

        } catch (\Exception $e) {
            \Log::error("Error fetching live price for symbol {$symbol}: " . $e->getMessage());
        }
    }

    return null; // Return null if no API could fetch the price
    }





}


