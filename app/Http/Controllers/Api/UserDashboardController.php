<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Package;
use App\Models\Trade;
use App\Models\Order;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserDashboardController extends Controller
{
    /**
     * Show the user's dashboard.
     */
    public function showDashboard(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        // User Information Overview
        $profilePicture = $user->profile->profile_picture ?? null;
        $name = $user->username;
        $profile = $this->getUserProfile($user);
        $purchasedPackages = $this->getUserPurchasedPackages($user);
        $trades = $this->getUserTrades($user);

        return response()->json([
            'profile_picture' => $profilePicture,
            'id' => $user->id,
            'name' => $name,
            'profile' => $profile,
            'purchased_packages' => $purchasedPackages,
            'trades' => $trades,
        ]);
    }

    // Helper Methods

    // Get the user's profile information
    private function getUserProfile($user)
    {
        return UserProfile::with(['country', 'city'])
            ->where('user_id', $user->id)
            ->first();
    }

    // Get the list of purchased packages
    private function getUserPurchasedPackages($user)
    {
        return Order::where('user_id', $user->id)
            ->where('order_status_id', 2) // Assuming status 2 means "completed"
            ->with('package') // No need to load duration separately if expiry_date exists
            ->get()
            ->map(function ($order) {
                $expiryDate = $order->expiry_date;
                $remainingDays = now()->diffInDays($expiryDate, false);
                $status = $remainingDays < 0 ? 'expired' : 'active';
    
                return [
                    'package_id' => $order->package->id,
                    'package_name' => $order->package->name,
                    'price' => $order->package->price,
                    'purchase_date' => $order->created_at,
                    'expiry_date' => $expiryDate,
                    'remaining_days' => max(0, $remainingDays), // Ensures no negative values
                    'status' => $status,
                ];
            });
    }
    
    
    

    // Get the user's trades
    private function getUserTrades($user)
    {
        // Get all purchased packages (including expired ones)
        $packages = Package::whereIn('id', function ($query) use ($user) {
                $query->select('package_id')
                    ->from('orders')
                    ->where('user_id', $user->id)
                    ->where('order_status_id', 2);
            })
            ->with([
                'trader', 
                'orders' => function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->where('order_status_id', 2);
                }
            ])
            ->get();
    
        return $packages->map(function ($package) use ($user) {
            // Get the latest expiry date for this user's order of the package
            $latestExpiryDate = $package->orders->max('expiry_date');
    
            // Fetch trades only before expiry
            $trades = Trade::where('package_id', $package->id)
                ->where('created_at', '<=', $latestExpiryDate)
                ->with(['marketPair', 'signalPerformance' => function ($query) {
                    $query->latest();
                }])
                ->get()
                ->map(function ($trade) use ($user) {
                    return [
                        'trade_id' => $trade->id,
                        'symbol' => $trade->marketPair->symbol ?? null,
                        'entry_price' => $trade->entry_price,
                        'take_profit' => $trade->take_profit,
                        'stop_loss' => $trade->stop_loss,
                        'profit_loss' => $trade->profit_loss,
                        'status' => $trade->status,
                        'created_at' => $trade->created_at,
    
                        // User Follow Status
                        'is_followed' => $trade->isFollowedByUser($user->id),
    
                        // Performance Data
                        'performance' => $trade->signalPerformance->map(function ($performance) {
                            return [
                                'current_price' => $performance->current_price,
                                'profit_loss' => $performance->profit_loss,
                                'status' => $performance->status,
                                'updated_at' => $performance->updated_at,
                            ];
                        }),
                    ];
                });
    
            return [
                'package' => [
                    'id' => $package->id,
                    'trades' => $trades, // Show only trades before expiry
                   
                ]
               
            ];
        });
    }
    
    
    

}
