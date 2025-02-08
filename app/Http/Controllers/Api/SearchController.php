<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserProfile;
use App\Models\Package;
use App\Models\Order;

class SearchController extends Controller
{
    // Search traders (user_profiles where trader column is 1)
    // Search traders by username (where trader = 1)
    public function searchTraders(Request $request)
    {
        $query = $request->input('query');
        $user = auth()->user();
    
        if (!$query) {
            return response()->json(['message' => 'Query parameter is missing'], 400);
        }
    
        // Search traders with additional relationships
        $traders = UserProfile::where('trader', 1)
            ->whereHas('user', function ($q) use ($query) {
                $q->where('username', 'like', '%' . $query . '%');
            })
            ->with(['user', 'badge', 'packages', 'country', 'city']) // Removed 'reviews'
            ->get();
    
        if ($traders->isEmpty()) {
            return response()->json(['message' => 'No traders found'], 404);
        }
    
        // Format response with ratings, reviews, and FAQs
        $traderData = $traders->map(function ($trader) use ($user) {
            $hasPurchased = $user && \DB::table('orders')
                ->where('user_id', $user->id)
                ->whereIn('package_id', function ($query) use ($trader) {
                    $query->select('id')->from('packages')->where('user_id', $trader->user->id);
                })
                ->exists();
    
            return [
                'id' => $trader->id,
                'username' => $trader->user->username,
                'rating' => $trader->rating,
                'short_info' => $trader->short_info,
                'total_signals' => $trader->total_signals,
                'total_packages' => $trader->total_packages,
                'win_percentage' => $trader->win_percentage,
                'rrr' => $trader->rrr,
                'status' => $trader->status,
                'users_count' => $trader->users_count,
                'about' => $trader->about,
                'deals_in' => $trader->deals_in,
                'contact_info' => $hasPurchased ? $trader->contact_info : '***',
                'member_since' => $trader->member_since,
                'average_response_time' => $trader->average_response_time,
                'location' => $trader->location,
                'country' => $trader->country ? $trader->country->name : null,
                'city' => $trader->city ? $trader->city->name : null,
                'profile_picture' => $trader->picture_url,
                'badge' => $trader->badge ? $trader->badge->name : null,
    
                // Packages
                'packages' => $trader->packages->map(function ($package) {
                    return [
                        'id' => $package->id,
                        'name' => $package->name,
                        'price' => $package->price,
                        'duration' => $package->duration->name ?? null,
                        'profit_loss' => $package->profit_loss,
                    ];
                }),
    
                // Signals (Trades)
                'trades' => $trader->packages->flatMap(function ($package) use ($hasPurchased) {
                    return $package->trades->map(function ($signal) use ($hasPurchased) {
                        return [
                            'id' => $signal->id,
                            'name' => $signal->name,
                            'coin_pair' => $signal->coinpair,
                            'entry' => $hasPurchased ? $signal->entry : '***',
                            'take_profit' => $hasPurchased ? $signal->take_profit : '***',
                            'stop_loss' => $hasPurchased ? $signal->stop_loss : '***',
                            'profit_loss' => $signal->profit_loss,
                        ];
                    });
                }),
    
                // Reviews (Now correctly accessed from user)
                'reviews' => $trader->user->reviews->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'user' => $review->user->username ?? 'Anonymous',
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'created_at' => $review->created_at->format('Y-m-d'),
                    ];
                }),
                 // Reviews (Now correctly accessed from user)
                 'faqs' => $trader->user->faqs->map(function ($faqs) {
                    return [
                        'id' => $faqs->id,
                        'question' => $faqs->question ?? 'Anonymous',
                        'answer' => $faqs->answer,
                       
                    ];
                }),
    
                
            ];
        });
    
        return response()->json($traderData, 200);
    }
    



    // Search packages by package name or trader name
    public function searchPackages(Request $request)
    {
        $query = $request->input('query'); // Search query

        if (!$query) {
            return response()->json(['message' => 'Query parameter is missing'], 400);
        }

        // Search by package name or by trader name (from users)
        $packages = Package::where('name', 'like', '%' . $query . '%')
            ->orWhereHas('user', function ($q) use ($query) {
                $q->where('username', 'like', '%' . $query . '%');
            })
            ->get();

        if ($packages->isEmpty()) {
            return response()->json(['message' => 'No packages found for the given query'], 404);
        }

        return response()->json($packages, 200);
    }
}
