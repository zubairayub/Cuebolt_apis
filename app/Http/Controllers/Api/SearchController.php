<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserProfile;
use App\Models\Package;

class SearchController extends Controller
{
     // Search traders (user_profiles where trader column is 1)
     // Search traders by username (where trader = 1)
    public function searchTraders(Request $request)
    {
        $query = $request->input('query'); // Get search query

        if (!$query) {
            return response()->json(['message' => 'Query parameter is missing'], 400);
        }

        // Search for traders (user_profiles where trader = 1 and user.username matches)
        $traders = UserProfile::where('trader', 1)
            ->whereHas('user', function($q) use ($query) {
                $q->where('username', 'like', '%' . $query . '%');
            })
            ->get();

        if ($traders->isEmpty()) {
            return response()->json(['message' => 'No traders found'], 404);
        }

        return response()->json($traders, 200);
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
             ->orWhereHas('user', function($q) use ($query) {
                 $q->where('username', 'like', '%' . $query . '%');
             })
             ->get();
 
         if ($packages->isEmpty()) {
             return response()->json(['message' => 'No packages found for the given query'], 404);
         }
 
         return response()->json($packages, 200);
     }
}
