<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package; // Import the Package model
use App\Models\User; // Import the Package model
use Stripe\Stripe;
use Stripe\Product;
use Stripe\Price;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;



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
            $packagesQuery = Package::with(relations: [
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
                ->select('id', 'stripe_product_id', 'stripe_price_id', 'name', 'description', 'signals_count', 'risk_reward_ratio', 'price', 'picture', 'user_id', 'duration_id')
                ->withCount([
                    'orders as active_orders' => function ($query) {
                        $query->where('expiry_date', '>', Carbon::now());
                    }
                ])->orderBy('id', 'desc');

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




    public function getMyPackagesTraders(Request $request)
    {
        try {
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);

            // Determine if the user is a trader
            $isTrader = $request->input('is_trader') == true;

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
                'duration:id,duration_name', // Eager load duration details
                'trades:id,package_id,trade_name,entry_price,take_profit,take_profit_2,stop_loss,status,profit_loss,time_frame,validity,market_pair_id,trade_type_id',
                'trades.marketPair:id,symbol', // Eager load market pair
                'trades.tradeType:id,name', // Eager load trade type
                'trades.signalPerformance:id,signal_id,current_price,profit_loss,entry_price,take_profit,stop_loss',

            ])
                ->select('id', 'name', 'description', 'signals_count', 'risk_reward_ratio', 'price', 'picture', 'user_id', 'duration_id')
                ->withCount([
                    'orders as active_orders' => function ($query) {
                        $query->where('expiry_date', '>', Carbon::now());
                    }
                ]);

            if ($isTrader) {

                // If user is a trader, check if they have any packages
                $packagesQuery->where('user_id', $request->user()->id);
            } else {

                // If user is not a trader, check if they have purchased packages
                $packagesQuery->whereHas('orders', function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id)
                        ->where('expiry_date', '>', Carbon::now());
                });
            }

            // Apply additional filters if provided in the request
            // if ($request->has('package_id')) {
            //     $packagesQuery->where('id', $request->input('package_id'));
            // }

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
                    $query->where('rating', '>=', $request->input('rating'));
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
    // public function store(Request $request)
    // {
    //     // Validate the request input
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|max:255',
    //         'description' => 'nullable|string',
    //         //'package_type' => 'required|in:daily,weekly,monthly,yearly,bi_yearly',
    //         'signals_count' => 'required|integer',
    //         'risk_reward_ratio' => 'required|numeric',
    //         'price' => 'required|numeric',
    //         'duration_id' => 'required|exists:durations,id',
    //         'picture' => 'nullable|url',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()], 400);
    //     }


    //     // Create a new package
    //     $package = new Package();
    //     $package->user_id = Auth::id();  // Store the authenticated user's ID
    //     $package->name = $request->name;
    //     $package->description = $request->description;
    //     $package->package_type = $request->package_type;
    //     $package->signals_count = $request->signals_count;
    //     $package->risk_reward_ratio = $request->risk_reward_ratio;
    //     $package->price = $request->price;
    //     $package->duration_id = $request->duration_id;
    //     $package->picture = $request->picture;
    //     $package->status = 1;  // Default to active
    //     $package->save();

    //     //make user trader
    //     makeUserTrader();

    //     return response()->json([
    //         'status' => 'success',
    //         'data' => $package,
    //         'message' => 'Package created successfully.'
    //     ], 201);
    // }


    public function store(Request $request)
    {



        // Log the incoming request data
        Log::channel('package_logs')->info('Incoming Request:', [
            'user_id' => Auth::id(),
            'request_data' => $request->all(),
        ]);
        // Validate the request input
        $validator = Validator($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'package_type' => 'required|in:daily,weekly,monthly,yearly,bi_yearly',
            'signals_count' => 'required|integer|min:1', // Ensure positive signals count
            'risk_reward_ratio' => 'required|numeric|min:0', // Ensure non-negative RRR
            'price' => 'required|numeric|min:0', // Ensure non-negative price
            'duration_id' => 'required|exists:durations,id',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Image validation
            'is_challenge' => 'nullable|boolean',  // New field: optional and boolean
            'market_type_id' => 'nullable|exists:trading_markets,id', // Foreign Key validation
            'achieved_rrr' => 'nullable|numeric|min:0', // Nullable field for achieved RRR
            'from_amount' => 'nullable|numeric|min:0', // Nullable field for 'from' amount
            'to_amount' => 'nullable|numeric|min:0', // Nullable field for 'to' amount
            'challenge_days' => 'nullable|integer|min:1', // Nullable field for challenge days
        ]);

        // If validation fails, return error with details
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }


        try {
            $userId = Auth::id();
            $basePath = "uploads/users/{$userId}/package";

            // Ensure the user-specific folder structure exists
            if (!Storage::disk('public')->exists($basePath)) {
                Storage::disk('public')->makeDirectory($basePath);
            }

            // Handle image upload if exists
            $picturePath = null;
            if ($request->hasFile('picture')) {
                // Save file inside the user-specific folder and ensure proper storage path
                $picturePath = $request->file('picture')->store($basePath, 'public');
            } else {
                // Set default picture path if no image is uploaded
                $picturePath = 'uploads/images/packages/default_package_picture.png';
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
            $package->picture = $picturePath; // Store the file path
            $package->is_challenge = $request->is_challenge ?? false;  // Default to false if not provided
            $package->market_type_id = $request->market_type_id;  // Foreign key, optional
            $package->achieved_rrr = $request->achieved_rrr;  // Nullable field
            $package->from_amount = $request->from_amount;  // Nullable field
            $package->to_amount = $request->to_amount;  // Nullable field
            $package->challenge_days = $request->challenge_days;  // Nullable field

            // Make user a trader (assuming this is a method you have for assigning roles)
            makeUserTrader();

            // Save the package to the database
            $package->save();

            // Execute the query and get the first matching user
            $user = Auth::user();
            $title = "Package Created";
            $body = "View Package";
            $type = "Package Created";
            $token = $user->fcm_token;
            $data = [];

            if ($token) {
                //  send_push_notification($tokens, $title, $body, $data, $type, $userIds);  // Sending notification for each token
            }

            // Call the createGroup API
            // Http::post('http://127.0.0.1:8000/api/create-group', [
            //     'packageId' => $package->id,
            //     'traderId' => $package->user_id,
            // ]);

            // Set Stripe API Key
            Stripe::setApiKey(env('STRIPE_SECRET'));

            // Create a Stripe Product
            $product = Product::create([
                'name' => $package->name,
                'metadata' => [
                    'trader_id' => $package->user_id,
                    'package_id' => $package->id,
                ],
            ]);

            // Create a Stripe Price
            $price = Price::create([
                'unit_amount' => $package->price * 100, // Amount in cents
                'currency' => 'usd',
                'recurring' => ['interval' => 'month'],
                'product' => $product->id,
            ]);

            // Update the package with Stripe IDs
            $updated = $package->update([
                'stripe_product_id' => $product->id,
                'stripe_price_id' => $price->id,
            ]);

            Log::channel('package_logs')->info('Update Result:', ['updated' => $updated]);

            // Log success response
            Log::channel('package_logs')->info('Package Created Successfully:', [
                'package_id' => $package->id,
                'package_data' => $package->toArray(),
                'stripe_data' => $product,
                'stripe__price_data' => $price,
            ]);

            // Return success response
            return redirect()->route('home')->with('success', 'Login successful');

        } catch (\Exception $e) {
            Log::channel('package_logs')->error('Exception Occurred:', [
                'error_message' => $e->getMessage(),
            ]);
            // Handle any exceptions (e.g., database errors, unexpected issues)
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating the package.',
                'error' => $e->getMessage(),
            ], 500); // Internal Server Error status
        }
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
    // public function update(Request $request, $id)
    // {
    //     // Find the package belonging to the authenticated user
    //     $package = Package::where('user_id', Auth::id())->find($id);

    //     // If the package is not found, return an error message
    //     if (!$package) {
    //         return response()->json(['message' => 'Package not found.'], 404);
    //     }

    //     // Validate the request input
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|max:255',
    //         'description' => 'nullable|string',
    //         'package_type' => 'required|in:daily,weekly,monthly,yearly,bi_yearly',
    //         'signals_count' => 'required|integer|min:1', // Ensure positive signals count
    //         'risk_reward_ratio' => 'required|numeric|min:0', // Ensure non-negative RRR
    //         'price' => 'required|numeric|min:0', // Ensure non-negative price
    //         'duration_id' => 'required|exists:durations,id',
    //         'picture' => 'nullable|url',
    //         'is_challenge' => 'nullable|boolean',  // Optional boolean field
    //         'market_type_id' => 'nullable|exists:trading_markets,id', // Foreign Key validation
    //         'achieved_rrr' => 'nullable|numeric|min:0', // Nullable field for achieved RRR
    //         'from_amount' => 'nullable|numeric|min:0', // Nullable field for 'from' amount
    //         'to_amount' => 'nullable|numeric|min:0', // Nullable field for 'to' amount
    //         'challenge_days' => 'nullable|integer|min:1', // Nullable field for challenge days
    //     ]);

    //     // If validation fails, return the errors
    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()], 400);
    //     }

    //     // Update the package details with the validated request data
    //     $package->name = $request->name;
    //     $package->description = $request->description;
    //     $package->package_type = $request->package_type;
    //     $package->signals_count = $request->signals_count;
    //     $package->risk_reward_ratio = $request->risk_reward_ratio;
    //     $package->price = $request->price;
    //     $package->duration_id = $request->duration_id;
    //     $package->picture = $request->picture;

    //     // Optional fields (only update if provided)
    //     if ($request->has('is_challenge')) {
    //         $package->is_challenge = $request->is_challenge;
    //     }
    //     if ($request->has('market_type_id')) {
    //         $package->market_type_id = $request->market_type_id;
    //     }
    //     if ($request->has('achieved_rrr')) {
    //         $package->achieved_rrr = $request->achieved_rrr;
    //     }
    //     if ($request->has('from_amount')) {
    //         $package->from_amount = $request->from_amount;
    //     }
    //     if ($request->has('to_amount')) {
    //         $package->to_amount = $request->to_amount;
    //     }
    //     if ($request->has('challenge_days')) {
    //         $package->challenge_days = $request->challenge_days;
    //     }

    //     // Save the updated package
    //     $package->save();

    //     // Return a success response with the updated package data
    //     return response()->json([
    //         'message' => 'Package updated successfully.',
    //         'data' => $package
    //     ], 200);
    // }
    public function update(Request $request, $id)
    {
        // Log the incoming request data
        Log::channel('trades_logs')->info('Incoming Request:', [
            'user_id' => Auth::id(),
            'request_data' => $request->all(),
        ]);

        // Step 1: Find the package belonging to the authenticated user
        $package = Package::where('user_id', Auth::id())->find($id);

        // If the package is not found, return an error message
        if (!$package) {
            return response()->json(['message' => 'Package not found.'], 404);
        }

        // Step 2: Validate the request input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'package_type' => 'required|in:daily,weekly,monthly,yearly,bi_yearly',
            'signals_count' => 'required|integer|min:1', // Ensure positive signals count
            'risk_reward_ratio' => 'required|numeric|min:0', // Ensure non-negative RRR
            'price' => 'required|numeric|min:0', // Ensure non-negative price
            'duration_id' => 'required|exists:durations,id',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Image validation
            'is_challenge' => 'nullable|boolean',  // Optional boolean field
            'market_type_id' => 'nullable|exists:trading_markets,id', // Foreign Key validation
            'achieved_rrr' => 'nullable|numeric|min:0', // Nullable field for achieved RRR
            'from_amount' => 'nullable|numeric|min:0', // Nullable field for 'from' amount
            'to_amount' => 'nullable|numeric|min:0', // Nullable field for 'to' amount
            'challenge_days' => 'nullable|integer|min:1', // Nullable field for challenge days
        ]);

        // If validation fails, return the errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            // Step 3: Handle image upload if exists
            $picturePath = $package->picture; // Keep the existing picture by default
            if ($request->hasFile('picture')) {
                $userId = Auth::id();
                $basePath = "uploads/users/{$userId}/package";

                // Ensure the user-specific folder structure exists
                if (!Storage::disk('public')->exists($basePath)) {
                    Storage::disk('public')->makeDirectory($basePath);
                }

                // Save new file inside the user-specific folder and delete the old one if needed
                if ($picturePath && Storage::disk('public')->exists($picturePath)) {
                    Storage::disk('public')->delete($picturePath);
                }

                $picturePath = $request->file('picture')->store($basePath, 'public');
            }

            // Step 4: Update the package details with the validated request data
            $package->update([
                'name' => $request->name,
                'description' => $request->description,
                'package_type' => $request->package_type,
                'signals_count' => $request->signals_count,
                'risk_reward_ratio' => $request->risk_reward_ratio,
                'price' => $request->price,
                'duration_id' => $request->duration_id,
                'picture' => $picturePath,
                'is_challenge' => $request->is_challenge ?? $package->is_challenge,
                'market_type_id' => $request->market_type_id ?? $package->market_type_id,
                'achieved_rrr' => $request->achieved_rrr ?? $package->achieved_rrr,
                'from_amount' => $request->from_amount ?? $package->from_amount,
                'to_amount' => $request->to_amount ?? $package->to_amount,
                'challenge_days' => $request->challenge_days ?? $package->challenge_days,
            ]);

            // Log success response
            Log::channel('package_logs')->info('Package Updated Successfully:', [
                'package_id' => $package->id,
                'package_data' => $package->toArray(),
            ]);

            // Step 5: Return a success response with the updated package data
            return response()->json([
                'message' => 'Package updated successfully.',
                'data' => $package,
                'image' => asset("storage/{$package->picture}"),
            ], 200);

        } catch (\Exception $e) {
            Log::channel('package_logs')->error('Exception Occurred During Update:', [
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the package.',
                'error' => $e->getMessage(),
            ], 500);
        }
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


    // public function fetchTopPackages(Request $request)
    // {
    //     // Retrieve the 'from_date' and 'to_date' from the request
    //     $fromDate = $request->input('from_date');
    //     $toDate = $request->input('to_date');

    //     // Start with the base query for active packages
    //     $query = Package::where('status', 1);

    //     // Filter packages by the date range if provided
    //     if ($fromDate && $toDate) {
    //         // Ensure both dates are in the proper format (e.g., 'Y-m-d')
    //         $query->whereBetween('created_at', [$fromDate, $toDate]);
    //     }

    //     // Retrieve the top packages, ordered by win percentage
    //     $topPackages = $query->with('trades') // Eager load the trades relation
    //         ->orderByDesc('win_percentage')
    //         ->get();

    //     // Calculate the total number of trades across all packages using the trades relation
    //     $totalTrades = $topPackages->sum(function ($package) {
    //         return $package->trades->count(); // Counting the number of trades for each package
    //     });

    //     // Calculate the average win rate of all packages
    //     $totalWinRate = $topPackages->sum('win_percentage');
    //     $averageWinRate = $topPackages->count() > 0 ? $totalWinRate / $topPackages->count() : 0;

    //     // Calculate the average RRR (achieved_risk_reward_ratio)
    //     $totalRRR = $topPackages->sum('achieved_rrr');
    //     $averageRRR = $topPackages->count() > 0 ? $totalRRR / $topPackages->count() : 0;

    //     // Return the filtered data as JSON, along with the average win rate and average RRR
    //     return response()->json([
    //         'packages' => $topPackages,
    //         'average_win_rate' => round($averageWinRate, 2), // Round to 2 decimal places
    //         'average_rrr' => round($averageRRR, 2), // Round to 2 decimal places
    //         'total_trades' => $totalTrades // Total number of trades
    //     ]);

    // }




    public function fetchTopPackages(Request $request)
    {
        $username = $request->input('username');
        // Find the user by username
        $user = User::where('username', $username)->first();

        // If the user does not exist, return an error response
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        // Retrieve the 'from_date' and 'to_date' from the request
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Start with the base query for active packages belonging to the user
        $query = Package::where('user_id', $user->id);

        // Filter packages by the date range if provided
        if ($fromDate && $toDate) {
            // Ensure both dates are in the proper format (e.g., 'Y-m-d')
            $query->whereBetween('created_at', [$fromDate, $toDate]);
        }

        // Retrieve the top packages, ordered by win percentage
        // $topPackages = $query->with('trades.marketPair') // Eager load the trades and marketPair relationship
        //     ->orderByDesc('win_percentage')
        //     ->get();
        $topPackages = $query->where('user_id', $user->id) // Filter by user_id
            ->with('trades.marketPair') // Eager load the trades and marketPair relationship
            ->orderByDesc('win_percentage')
            ->get();


        // Initialize variables for calculations
        $totalPackages = 0;
        $totalTrades = 0;
        $winCount = 0;
        $lossCount = 0;
        $totalWinRate = 0;
        $totalRRR = 0;
        $mostTradedPair = null;
        $bestPerformingPair = null;
        $pairTradeCounts = [];
        $pairWinRates = [];
        $processedPackageIds = [];  // Array to track unique package_ids

        // Loop through the packages to calculate various statistics
        foreach ($topPackages as $package) {
            $totalTrades += $package->trades->count();
            foreach ($package->trades as $trade) {
                // Calculate win/loss count (Assuming 'profit_loss' is positive for win, negative for loss)
                if ($trade->profit_loss > 0) {
                    $winCount++;
                } elseif ($trade->profit_loss < 0) {
                    $lossCount++;
                }

                if (!in_array($package->id, $processedPackageIds)) {
                    // If not processed, accumulate the values
                    $totalWinRate += $package->win_percentage;
                    $totalRRR += $package->achieved_rrr;

                    // Add the current package_id to the processed list
                    $processedPackageIds[] = $package->id;
                    $totalPackages++;  // Count the number of unique packages
                }

                // Count the number of trades for each pair
                $pair = $trade->marketPair->symbol; // Get the symbol of the market pair (assuming it's available in MarketPair model)
                if (!isset($pairTradeCounts[$pair])) {
                    $pairTradeCounts[$pair] = 0;
                }
                $pairTradeCounts[$pair]++;

                // Calculate win rate for each pair
                if (!isset($pairWinRates[$pair])) {
                    $pairWinRates[$pair] = ['wins' => 0, 'trades' => 0];
                }
                $pairWinRates[$pair]['trades']++;
                if ($trade->profit_loss > 0) {
                    $pairWinRates[$pair]['wins']++;
                }
            }
        }

        // Calculate average win rate and average RRR
        // Calculate average win rate and average RRR
        $averageWinRate = ($totalPackages > 0) ? $totalWinRate / $totalPackages : 0;  // Average win rate (no need to multiply by 100)
        $averageRRR = ($totalPackages > 0) ? $totalRRR / $totalPackages : 0;  // Average RRR (no need to multiply by 100)

        // Find the most traded pair
        arsort($pairTradeCounts); // Sort by the number of trades in descending order
        $mostTradedPair = key($pairTradeCounts); // Get the pair with the most trades

        // Find the best performing pair (highest win rate)
        $bestPerformingPair = null;
        $bestWinRate = 0;
        foreach ($pairWinRates as $pair => $data) {
            $winRate = $data['wins'] / $data['trades'] * 100; // Calculate win rate as percentage
            if ($winRate > $bestWinRate) {
                $bestWinRate = $winRate;
                $bestPerformingPair = $pair;
            }
        }

        // Return the filtered data as JSON, along with additional calculated data
        return response()->json([
            'packages' => $topPackages,
            'average_win_rate' => round($averageWinRate, 2), // Round to 2 decimal places
            'average_rrr' => round($averageRRR, 2), // Round to 2 decimal places
            'total_trades' => $totalTrades, // Total number of trades
            'win_count' => $winCount, // Total wins
            'loss_count' => $lossCount, // Total losses
            'most_traded_pair' => $mostTradedPair, // Most traded pair
            'best_performing_pair' => $bestPerformingPair, // Best performing pair
            'win_loss_ratio' => $winCount > 0 ? ($lossCount / $winCount) : 0 // Win/Loss ratio
        ]);
    }

}
