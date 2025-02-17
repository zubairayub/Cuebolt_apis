<?php

namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;

use App\Models\UserProfile;
use App\Models\User;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class UserProfileController extends Controller
{


    /**
     * Create a static profile for the user after registration.
     *
     * @param int $userId
     * @return void
     */
    public function createProfile(Request $request, $userId = null)
    {
        try {
            // Use provided userId or fallback to authenticated user
            $userId = $userId ?? Auth::id();

            if (!$userId) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // Check if request data is provided
            if ($request && $request->all()) {
                // Validate the request data if provided
                $validator = Validator::make($request->all(), [
                    'rating' => 'nullable|numeric|between:0,99.99', // Allow numeric values, e.g., 9.99
                    'short_info' => 'nullable|string|max:255',
                    'total_signals' => 'nullable|integer',
                    'total_packages' => 'nullable|integer',
                    'win_percentage' => 'nullable|numeric',
                    'rrr' => 'nullable|numeric',
                    'status' => 'nullable|string',
                    'users_count' => 'nullable|integer',
                    'about' => 'nullable|string',
                    'deals_in' => 'nullable|string',
                    'contact_info' => 'nullable|string',
                    'member_since' => 'nullable|date',
                    'average_response_time' => 'nullable|numeric',
                    'location' => 'nullable|string',
                    'languages' => 'nullable|array',
                    'country_id' => 'nullable|exists:countries,id', // Ensure country_id exists in countries table
                    'city_id' => 'nullable|exists:cities,id', // Ensure city_id exists in cities table
                ]);

                if ($validator->fails()) {
                    return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
                }
            }

            // Check if a profile already exists for the user
            $userProfile = UserProfile::firstOrNew(['user_id' => $userId]);

            // Log whether the profile was found or created new
            Log::info('User Profile Found:', ['exists' => $userProfile->exists]);

            // If request is provided, update the profile with request data
            if ($request) {
                // Fill with request data
                $userProfile->fill([
                    'trader' => $request->input('trader', 0),
                    'rating' => $request->input('rating', 0),
                    'short_info' => $request->input('short_info', 'New trader in the market.'),
                    'total_signals' => $request->input('total_signals', 0),
                    'total_packages' => $request->input('total_packages', 0),
                    'win_percentage' => $request->input('win_percentage', 0),
                    'rrr' => $request->input('rrr', 0),
                    'status' => $request->input('status', 'offline'),
                    'users_count' => $request->input('users_count', 0),
                    'about' => $request->input('about', 'I am new to the crypto market.'),
                    'deals_in' => $request->input('deals_in', 'crypto'),
                    'contact_info' => $request->input('contact_info', ''),
                    'member_since' => $request->input('member_since', now()),
                    'average_response_time' => $request->input('average_response_time', 0),
                    'location' => $request->input('location', 'Unknown'),
                    'country_id' => $request->input('country_id', 1),
                    'city_id' => $request->input('location', 1),
                ]);
            } else {
                // If no request data is passed, set default static data
                $userProfile->fill([
                    'rating' => 0,
                    'short_info' => 'New trader in the market.',
                    'total_signals' => 0,
                    'total_packages' => 0,
                    'win_percentage' => 0,
                    'rrr' => 0,
                    'status' => 'offline',
                    'users_count' => 0,
                    'about' => 'I am new to the crypto market.',
                    'deals_in' => 'crypto',
                    'contact_info' => '',
                    'member_since' => now(),
                    'average_response_time' => 0,
                    'location' => 'Unknown',
                    'country_id' =>  1,
                    'city_id' =>  1,
                ]);
            }

            // Sync/update the languages if provided
            if ($request && $request->has('languages')) {
                $languages = $request->input('languages', ['English']); // Default to English if not provided
                $languageIds = Language::whereIn('name', $languages)->pluck('id');
                $userProfile->languages()->sync($languageIds);
            }

            // Save the profile
            $userProfile->save();
            return $userProfile; // Return the model instance directly
           // return response()->json(['message' => 'Profile updated successfully', 'data' => $userProfile], 200);

        } catch (\Exception $e) {
            Log::error('Error updating profile:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    

    
   // Get user profile
   public function showProfile(Request $request, $userId = null)
   {
       // Use provided userId or fallback to authenticated user
       $userId = $userId ?? Auth::id();

       // Check if the user is authenticated
       if (!$userId) {
           return response()->json(['message' => 'Unauthorized'], 401);
       }

       // Retrieve the profile based on userId
       $profile = UserProfile::where('user_id', $userId)->first();

       // Check if the profile exists
       if (!$profile) {
           return response()->json(['message' => 'Profile not found'], 404);
       }

       // Return the profile data
       return response()->json(['data' => $profile], 200);
   }


   public function getAllTraders()
   {
       $signalTraders = User::with([
           'profile' => function ($query) {
               $query->with([
                   'languages',  // Many-to-Many relationship with Language
                   'country',    // BelongsTo relationship with Country
                   'city',       // BelongsTo relationship with City
                   'badge',      // BelongsTo relationship with Badge
               ])->where('trader', 1);  // Filter by trader field in user_profiles
           },
           'packages' => function ($query) {  // Corrected to access packages directly on User model
               $query->with([
                   'orders',            // HasMany relationship with Orders
                   'trades.marketPair', // Trade relationships, with nested MarketPair
                   'trades.tradeType',  // Trade relationships, with nested TradeType
               ]);
           },
           'reviews' => function ($query) {  // Trader reviews
               $query->where('rating', '>=', 3); // Example condition, e.g., only reviews with rating 3+
           },
           'faqs',  // Assuming FAQs are related to the signal trader (can adjust if needed)
           'packages.trades' => function ($query) {
               $query->with(['marketPair', 'tradeType']); // Include trades with MarketPair and TradeType
           }
       ])->whereHas('profile', function ($query) {
           $query->where('trader', 1);  // Ensures only users with trader status in profile are retrieved
       })->get();
   
       return response()->json([
           'status' => true,
           'message' => 'Traders data retrieved successfully',
           'data' => [
               'traders' => $signalTraders
           ]
       ], 200);
   }
    
}
