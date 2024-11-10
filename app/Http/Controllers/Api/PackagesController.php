<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package; // Import the Package model
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class PackagesController extends Controller
{


     /**
     * Get all packages from all users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllPackages(Request $request)
    {
        try {
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);
            
            // Initialize the query
            $packagesQuery = Package::with([
                'user:id,username',
                'user.profile:id,user_id,profile_picture,rating,badge_id', // Include badge_id in the user profile
                'user.profile.badge:id,description,icon', // Eager load badge details
                'orders' => function ($query) {
                    $query->where('expiry_date', '>', Carbon::now());
                },
                'orders.buyer:id',
                'orders.buyer.profile:id,user_id,profile_picture',
                'duration:id,duration_name' // Eager load duration details
            ])
            ->select('id', 'name', 'description', 'signals_count', 'risk_reward_ratio', 'price', 'picture', 'user_id', 'duration_id')
            ->withCount(['orders as active_orders' => function ($query) {
                $query->where('expiry_date', '>', Carbon::now());
            }]);
    
            // Apply filtering based on request parameters
            if ($request->has('package_id')) {
                $packagesQuery->where('id', $request->input('package_id'));
            }
    
            if ($request->has('price')) {
                $packagesQuery->where('price', $request->input('price'));
            }
    
            if ($request->has('risk_reward_ratio')) {
                $packagesQuery->where('risk_reward_ratio', $request->input('risk_reward_ratio'));
            }
    
            if ($request->has('signals_count')) {
                $packagesQuery->where('signals_count', $request->input('signals_count'));
            }
            
            // Add rating filter for user profiles
            if ($request->has('rating')) {
                $packagesQuery->whereHas('user.profile', function ($query) use ($request) {
                    $query->where('rating', '>=', $request->input('rating')); // Assuming you want to filter by minimum rating
                });
            }
            
            // Get the packages with pagination
            $packages = $packagesQuery->paginate($limit);
    
            // Return the result as a JSON response
            return response()->json([
                'success' => true,
                'data' => $packages,
            ], 200);
    
        } catch (\Exception $e) {
            // Handle any errors and return a response
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve packages.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
   
   

    /**
     * Display a listing of the packages.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Retrieve all packages belonging to the authenticated user
        $packages = Package::where('user_id', Auth::id())->get();

        return response()->json([
            'status' => 'success',
            'data' => $packages
        ], 200);
    }

    /**
     * Store a newly created package in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the request input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'package_type' => 'required|in:daily,weekly,monthly,yearly,bi_yearly',
            'signals_count' => 'required|integer',
            'risk_reward_ratio' => 'required|numeric',
            'price' => 'required|numeric',
            'duration_id' => 'required|exists:durations,id',
            'picture' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        
        // Create a new package
        $package = new Package();
        $package->user_id = Auth::id();  // Store the authenticated user's ID
        $package->name = $request->name;
        $package->description = $request->description;
        $package->package_type = $request->package_type;
        $package->signals_count = $request->signals_count;
        $package->risk_reward_ratio = $request->risk_reward_ratio;
        $package->price = $request->price;
        $package->duration_id = $request->duration_id;
        $package->picture = $request->picture;
        $package->status = 1;  // Default to active
        $package->save();

        //make user trader
        makeUserTrader();
        
        return response()->json([
            'status' => 'success',
            'data' => $package,
            'message' => 'Package created successfully.'
        ], 201);
    }

    /**
     * Display the specified package.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $package = Package::where('user_id', Auth::id())->find($id);

        if (!$package) {
            return response()->json(['message' => 'Package not found.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $package
        ], 200);
    }

    /**
     * Update the specified package in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $package = Package::where('user_id', Auth::id())->find($id);

        if (!$package) {
            return response()->json(['message' => 'Package not found.'], 404);
        }

        // Validate the request input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'package_type' => 'required|in:daily,weekly,monthly,yearly,bi_yearly',
            'signals_count' => 'required|integer',
            'risk_reward_ratio' => 'required|numeric',
            'price' => 'required|numeric',
            'duration_id' => 'required|exists:durations,id',
            'picture' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Update the package details
        $package->name = $request->name;
        $package->description = $request->description;
        $package->package_type = $request->package_type;
        $package->signals_count = $request->signals_count;
        $package->risk_reward_ratio = $request->risk_reward_ratio;
        $package->price = $request->price;
        $package->duration_id = $request->duration_id;
        $package->picture = $request->picture;
        $package->status = 1;  // Default to active
        $package->save();

        return response()->json([
            'status' => 'success',
            'data' => $package,
            'message' => 'Package updated successfully.'
        ], 200);
    }

    /**
     * Remove the specified package from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $package = Package::where('user_id', Auth::id())->find($id);

        if (!$package) {
            return response()->json(['message' => 'Package not found.'], 404);
        }

        $package->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Package deleted successfully.'
        ], 200);
    }
}
