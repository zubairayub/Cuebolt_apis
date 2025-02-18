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
    // public function showDashboard(Request $request)
    // {
    //     // Get the authenticated trader
    //     $trader = Auth::user();

    //     // User Information Overview
    //     $profilePicture = $trader->profile->profile_picture ?? asset('default-avatar.png');
    //     $name = $trader->username;
    //     $profile = $this->getTraderProfile($trader);
    //     $earnings = $this->getTotalEarnings($trader);
    //     $signalFollowers = $this->getFollowersCount($trader);
    //     $totalSignals = $this->getTotalAndActiveSignals($trader);
    //     $successRate = $this->getSignalSuccessRate($trader);
    //     $signalsAndTopPerformer = $this->getSignalsAndTopPerformer($trader);
    //     $challengeProgress = $this->getChallengeProgress($trader);
    //     $rating = $this->getTraderRating($trader);
    //     $topSignals = $signalsAndTopPerformer['recent_signals'];
    //     $topPerformingSignals = $signalsAndTopPerformer['top_performer'];
    //     $topPackages = Package::where('status', 1)
    //         ->where('user_id', $trader->id)  // If applicable, filter based on user's ownership
    //         ->orderByDesc('win_percentage')
    //         ->get();
    //     // DD($trader);
    //     // Return data to the Blade view
    //     return view('inner-pages.trader-dashboard', compact(
    //         'profilePicture',
    //         'name',
    //         'profile',
    //         'signalFollowers',
    //         'totalSignals',
    //         'successRate',
    //         'topSignals',
    //         'topPerformingSignals',
    //         'earnings',
    //         'challengeProgress',
    //         'rating',
    //         'topPackages'
    //     ));
    // }




    public function showDashboard(Request $request)
    {
        try {
            // Get the authenticated trader
            $trader = Auth::user();

            // User Information Overview
            $profilePicture = $trader->profile->profile_picture ?? asset('default-avatar.png');
            $name = $trader->username;

            // Handle potential null values or empty data
            $profile = $this->getTraderProfile($trader) ?? [];  // Default to empty array if null
            $earnings = $this->getTotalEarnings($trader) ?? 0;  // Default to 0 if null
            $signalFollowers = $this->getFollowersCount($trader) ?? 0;  // Default to 0 if null
            $totalSignals = $this->getTotalAndActiveSignals($trader) ?? ['total' => 0, 'active' => 0];  // Default to empty array
            $successRate = $this->getSignalSuccessRate($trader) ?? 0;  // Default to 0 if null
            $signalsAndTopPerformer = $this->getSignalsAndTopPerformer($trader) ?? ['recent_signals' => [], 'top_performer' => []];  // Default to empty arrays
            $challengeProgress = $this->getChallengeProgress($trader) ?? 0;  // Default to 0 if null
            $rating = $this->getTraderRating($trader) ?? 0;  // Default to 0 if null

            // Get top packages, ensure it's always an array or collection
            $topPackages = Package::where('status', 1)
                ->where('user_id', $trader->id)  // If applicable, filter based on user's ownership
                ->orderByDesc('win_percentage')
                ->get();

            // Ensure that $topPackages is not null or empty
            if ($topPackages->isEmpty()) {
                $topPackages = collect();  // Use empty collection if no packages
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
        } catch (\Exception $e) {
            // Catch any unexpected errors and return a user-friendly error message
            // Log the error for further analysis
            //Log::error("Error loading trader dashboard: " . $e->getMessage());

            // Return a fallback view with an error message
            return view('inner-pages.trader-dashboard', [
                'error' => 'There was an issue loading your dashboard. Please try again later.'
            ]);
        }
    }



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
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get();

            // Fetch all trades for top performance evaluation
            $trades = Trade::whereIn('package_id', function ($query) use ($trader) {
                $query->select('id')
                    ->from('packages')
                    ->where('user_id', $trader->id);
            })
                ->get();

            // Calculate top-performing signal
            $topPerformingSignal = $this->getTopPerformingSignal($trades);

            return [
                'recent_signals' => $recentSignals,
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


    private function getLivePriceFromBinance($symbol)
    {
        try {
            $binanceSymbol = str_replace('/', '', strtoupper($symbol));
            $apiUrl = "https://api.binance.com/api/v3/ticker/price?symbol=" . strtoupper($binanceSymbol);

            $response = file_get_contents($apiUrl);
            $data = json_decode($response, true);

            if (isset($data['price'])) {
                return (float) $data['price'];
            }
        } catch (\Exception $e) {
            \Log::error("Error fetching live price for symbol {$symbol}: " . $e->getMessage());
        }

        return null; // Return null if unable to fetch price
    }





}


