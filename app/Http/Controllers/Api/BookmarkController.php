<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bookmark;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
     // Bookmark a trader profile
     public function bookmarkTrader($traderProfileId)
     {
         $user = Auth::user(); // Authenticated user
        
         // Check if trader profile exists
         $traderProfile = UserProfile::where('user_id', $traderProfileId)
                            ->where('trader', 1)
                            ->first();
 
         if (!$traderProfile || $traderProfile->trader != 1) {
             return response()->json(['message' => 'Trader profile not found'], 404);
         }
 
         // Check if already bookmarked
         $existingBookmark = Bookmark::where('user_id', $user->id)
             ->where('trader_profile_id', $traderProfile->id)
             ->first();
 
         if ($existingBookmark) {
             return response()->json(['message' => 'Already bookmarked'], 409);
         }
 
           // Check if the trader_profile_id exists in the database before inserting
            $bookmark = new Bookmark();
            $bookmark->user_id = Auth::id(); // Assuming you're using authenticated users
            $bookmark->trader_profile_id = $traderProfile->id;

            // Save the bookmark
            $bookmark->save();
 
         return response()->json(['message' => 'Trader profile bookmarked', 'bookmark' => $bookmark], 201);
     }
 
     // Remove a bookmark
     public function unbookmarkTrader($traderProfileId)
     {
         $user = Auth::user();
 
         // Find the bookmark
         $bookmark = Bookmark::where('user_id', $user->id)
             ->where('trader_profile_id', $traderProfileId)
             ->first();
 
         if (!$bookmark) {
             return response()->json(['message' => 'No bookmark found'], 404);
         }
 
         // Delete bookmark
         $bookmark->delete();
 
         return response()->json(['message' => 'Trader profile unbookmarked'], 200);
     }
}
