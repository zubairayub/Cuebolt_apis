<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Package;
use App\Models\Trade;
use App\Models\UserReview;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TraderDashboardController extends Controller
{
    /**
     * Show the trader's dashboard.
     */
    public function showDashboard(Request $request)
    {
        // Get the authenticated trader
        $trader = Auth::user();

        // User Information Overview
        $profilePicture = $trader->profile->profile_picture;  // Assuming this column exists in your users table
        $name = $trader->username;
        $badge = $this->getTraderBadge($trader);
        $earnings = $this->getTotalEarnings($trader);
        $usersEarnings = $this->getUsersEarnings($trader);
        
        // Earnings breakdown (daily, weekly, monthly)
        $earningsBreakdown = $this->getEarningsBreakdown($trader);

        // Trading Performance Metrics
        $overallRRR = $this->getOverallRRR($trader);
        $winRate = $this->getWinRate($trader);
        $totalSignals = $this->getTotalSignals($trader);
        $successRate = $this->getSignalSuccessRate($trader);

        // Challenge Progress
        $challengeProgress = $this->getChallengeProgress($trader);

        // Ratings and Reviews
        $rating = $this->getTraderRating($trader);

        // Return the dashboard data
        return response()->json([
            'profile_picture' => $profilePicture,
            'id' => $trader->id,
            'name' => $name,
            'badge' => $badge,
            'earnings' => $earnings,
            'users_earnings' => $usersEarnings,
            'earnings_breakdown' => $earningsBreakdown,
            'overall_rrr' => $overallRRR,
            'win_rate' => $winRate,
            'total_signals' => $totalSignals,
            'success_rate' => $successRate,
            'challenge_progress' => $challengeProgress,
            'rating' => $rating,
        ]);
    }

    // Helper Methods

    // Get the trader's badge (can be based on performance, level, or rank)
    private function getTraderBadge($trader)
    {
        // Example: Determine badge based on total earnings or number of signals shared
        if ($trader->total_earnings >= 5000) {
            return "Pro Trader";
        }
        return "Novice Trader";
    }

    // Get the total earnings from signal sales (in fiat and crypto)
    private function getTotalEarnings($trader)
    {
        // Assuming Package model represents packages sold by the trader
        $totalEarningsFiat = Package::where('user_id', $trader->id)->sum('price');
        $totalEarningsCrypto = Package::where('user_id', $trader->id)->sum('price'); // Example field for crypto earnings
        return [
            'fiat' => $totalEarningsFiat,
            'crypto' => $totalEarningsCrypto,
            'total' => $totalEarningsFiat + $totalEarningsCrypto
        ];
    }

    // Get the earnings breakdown (daily, weekly, monthly)
    private function getEarningsBreakdown($trader)
    {
        $now = Carbon::now();
        return [
            'daily' => $this->getEarningsByPeriod($trader, $now->subDay(), $now),
            'weekly' => $this->getEarningsByPeriod($trader, $now->subWeek(), $now),
            'monthly' => $this->getEarningsByPeriod($trader, $now->subMonth(), $now)
        ];
    }

    // Get earnings by period (Helper function)
    private function getEarningsByPeriod($trader, $startDate, $endDate)
    {
        return Package::where('user_id', $trader->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('price');
    }

    // Get the overall Risk-Reward Ratio (RRR)
    private function getOverallRRR($trader)
    {
        $signals = Trade::where('package_id', $trader->id)->get();
        $totalRisk = $signals->sum('risk');  // Assuming signals table has a risk column
        $totalReward = $signals->sum('reward');  // Assuming signals table has a reward column
        return $totalRisk > 0 ? $totalReward / $totalRisk : 0;
    }

    // Get the trader's win rate
    private function getWinRate($trader)
    {
        $signals = Trade::where('package_id', $trader->id)->get();
        $totalSignals = $signals->count();
        $successfulSignals = $signals->where('status', 'success')->count();
        return $totalSignals > 0 ? ($successfulSignals / $totalSignals) * 100 : 0;
    }

    // Get the total number of signals shared by the trader
    private function getTotalSignals($trader)
    {
        return Trade::where('package_id', $trader->id)->count();
    }

    // Get the success rate of signals
    private function getSignalSuccessRate($trader)
    {
        $totalSignals = $this->getTotalSignals($trader);
        $successfulSignals = Trade::where('package_id', $trader->id)->where('status', 'success')->count();
        return $totalSignals > 0 ? ($successfulSignals / $totalSignals) * 100 : 0;
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
        $averageRating = UserReview::where('user_id', $trader->id)->avg('rating'); // Assuming rating field exists in ratings table
        return round($averageRating, 1); // Round to 1 decimal place
    }

     // Get users' total earnings by trader
     private function getUsersEarnings(User $trader)
     {
         // Assuming users have a relationship with signals
        // return $trader->signals()->sum('profit');  // profit here refers to earnings per signal
     }
}


