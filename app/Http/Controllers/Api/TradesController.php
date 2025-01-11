<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trade;
use App\Models\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class TradesController extends Controller
{
    /**
     * Display a listing of the trades.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Check if `package_id` is provided in the request
            if ($request->has('package_id')) {
                // Fetch signals for the specified package
                $packageId = $request->input('package_id');
                $signals = Trade::where('package_id', $packageId)->paginate(10);
                return response()->json($signals, 200);
            }

            // Check if the user is authenticated and fetch their packages' signals
            if (auth()->check()) {
                // Get all package IDs associated with the authenticated user
                $packageIds = Package::where('user_id', auth()->id())->pluck('id');
                // Fetch signals for the authenticated user's packages
                $signals = Trade::whereIn('package_id', $packageIds)->paginate(10);
                return response()->json($signals, 200);
            }

            // If no parameters are provided, return all signals
            $signals = Trade::paginate(10);
            return response()->json($signals, 200);

        } catch (Exception $e) {
            // Return an error response in case of exception
            return response()->json(['error' => 'Failed to fetch data', 'message' => $e->getMessage()], 500);
        }
    }


    /**
     * Store a newly created trade in the database.
     *
     * @param Request $request
     * @return JsonResponse
     */
    // public function store(Request $request): JsonResponse
    // {
    //     // Step 1: Validation
    //     try {
    //         // Validate the request input
    //         $validated = $request->validate([
    //             'trade_name' => 'required|string|max:255',
    //             'package_id' => 'required|exists:packages,id',
    //             'market_pair_id' => 'required|exists:market_pairs,id',
    //             'trade_type_id' => 'required|exists:trade_types,id',
    //             'trade_date' => 'required|date',
    //             'entry_price' => 'required|numeric',
    //             'take_profit' => 'required|array|min:1|max:2', // Ensure it's an array with 1-2 elements
    //             'take_profit.*' => 'required|numeric', // Ensure each array element is numeric
    //             'stop_loss' => 'required|numeric',
    //             'time_frame' => 'required|string',
    //             'validity' => 'required|string',
    //             'status' => 'required|boolean',
    //         ]);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         // Log validation errors
    //         \Log::error('Validation failed during trade creation:', $e->errors());
    
    //         // Return validation errors to the client
    //         return response()->json(['errors' => $e->errors()], 422);
    //     }
    
    //     // Step 2: Authorization check
    //     try {
    //         // Check if the package belongs to the authenticated user
    //         $package = Package::findOrFail($validated['package_id']);
    //         if ($package->user_id !== auth()->id()) {
    //             \Log::warning('Unauthorized access attempt during trade creation.', [
    //                 'user_id' => auth()->id(),
    //                 'package_owner_id' => $package->user_id,
    //             ]);
    //             return response()->json(['error' => 'Unauthorized to create trade for this package'], 403); // HTTP Status 403: Forbidden
    //         }
    //     } catch (Exception $e) {
    //         \Log::error('Error during authorization check:', ['message' => $e->getMessage()]);
    //         return response()->json(['error' => 'Authorization check failed', 'message' => $e->getMessage()], 500);
    //     }
    
    //     // Step 3: Create the trade
    //     try {
    //         $takeProfit1 = $validated['take_profit'][0] ?? null; // First element or null
    //         $takeProfit2 = $validated['take_profit'][1] ?? null; // Second element or null
    //         // Prepare data for trade creation
    //         $tradeData = array_merge($validated, [
    //             'take_profit' => $takeProfit1,
    //             'take_profit_2' => $takeProfit2,
    //         ]);
    //         // Create the trade using the validated data
    //         $trade = Trade::create($tradeData);
    
    //         \Log::info('Trade created successfully.', ['trade_id' => $trade->id]);
    //         return response()->json($trade, 201); // HTTP Status 201: Created
    //     } catch (Exception $e) {
    //         // Log the error and return the error message
    //         \Log::error('Trade creation failed:', ['message' => $e->getMessage()]);
    //         return response()->json(['error' => 'Failed to create trade', 'message' => $e->getMessage()], 500);
    //     }
    // }

    public function store(Request $request): JsonResponse
{
    // Step 1: Validation
    try {
        // Validate the request input
        $validated = $request->validate([
            'trade_name' => 'required|string|max:255',
            'package_id' => 'required|exists:packages,id',
            'market_pair_id' => 'required|exists:market_pairs,id',
            'trade_type_id' => 'required|exists:trade_types,id',
            'trade_date' => 'required|date',
            'entry_price' => 'required|numeric',
            'take_profit' => 'required|array|min:1|max:2', // Ensure it's an array with 1-2 elements
            'take_profit.*' => 'required|numeric', // Ensure each array element is numeric
            'stop_loss' => 'required|numeric',
            'time_frame' => 'required|string',
            'validity' => 'required|string',
            'status' => 'required|boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Image validation
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Validation failed during trade creation:', $e->errors());
        return response()->json(['errors' => $e->errors()], 422);
    }

    // Step 2: Authorization check
    try {
        $package = Package::findOrFail($validated['package_id']);
        if ($package->user_id !== auth()->id()) {
            \Log::warning('Unauthorized access attempt during trade creation.', [
                'user_id' => auth()->id(),
                'package_owner_id' => $package->user_id,
            ]);
            return response()->json(['error' => 'Unauthorized to create trade for this package'], 403);
        }
    } catch (Exception $e) {
        \Log::error('Error during authorization check:', ['message' => $e->getMessage()]);
        return response()->json(['error' => 'Authorization check failed', 'message' => $e->getMessage()], 500);
    }

    // Step 3: Create the trade
    try {
        $takeProfit1 = $validated['take_profit'][0] ?? null;
        $takeProfit2 = $validated['take_profit'][1] ?? null;

        // Prepare data for trade creation
        $tradeData = array_merge($validated, [
            'take_profit' => $takeProfit1,
            'take_profit_2' => $takeProfit2,
        ]);

        // Create the trade using the validated data
        $trade = Trade::create($tradeData);

        // Step 4: Save Images (if any)
        if ($request->hasFile('images')) {
            $userId = auth()->id(); // Get the authenticated user's ID
        
            // Define the folder path
            $basePath = "uploads/users/{$userId}/trades";
        
            // Ensure the directory exists
            if (!Storage::exists($basePath)) {
                Storage::makeDirectory($basePath); // Creates the directory if it doesn't exist
            }
        
            foreach ($request->file('images') as $image) {
                // Store the file in the user's trades folder
                $filePath = $image->store($basePath, 'public');
        
                // Optional: Save image details in the database
                $trade->images()->create([
                    'image_path' => $filePath,
                    'image_name' => $image->getClientOriginalName(),
                ]);
            }
        
           // return response()->json(['message' => 'Images uploaded successfully.'], 201);
        } else {
            \Log::info('Trade Created without images.', ['trade_id' => $trade->id]);
        }
        

        \Log::info('Trade created successfully with images.', ['trade_id' => $trade->id]);
        return response()->json($trade->load('images'), 201); // Include images in the response
    } catch (Exception $e) {
        \Log::error('Trade creation failed:', ['message' => $e->getMessage()]);
        return response()->json(['error' => 'Failed to create trade', 'message' => $e->getMessage()], 500);
    }
}

    

    /**
     * Display the specified trade.
     *
     * @param Trade $trade
     * @return JsonResponse
     */
    public function show(Trade $trade): JsonResponse
    {
        try {
            // Check if the trade's package belongs to the authenticated user
            if ($trade->package->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized access'], 403); // HTTP Status 403: Forbidden
            }

            return response()->json($trade, 200); // HTTP Status 200
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Trade not found', 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch trade', 'message' => $e->getMessage()], 500);
        }
    }

   
//     public function update(Request $request, Trade $trade): JsonResponse
// {
//     // Step 1: Validation
//     try {
//         // Validate the request input
//         $validated = $request->validate([
//             'trade_name' => 'required|string|max:255',
//             'market_pair_id' => 'required|exists:market_pairs,id',
//             'trade_type_id' => 'required|exists:trade_types,id',
//             'trade_date' => 'required|date',
//             'entry_price' => 'required|numeric',
//             'take_profit' => 'required|numeric',
//             'stop_loss' => 'required|numeric',
//             'time_frame' => 'required|string',
//             'validity' => 'required|string',
//             'status' => 'required|boolean',
//         ]);
//     } catch (\Illuminate\Validation\ValidationException $e) {
//         // Log validation errors
//         \Log::error('Validation failed during trade update:', $e->errors());

//         // Return validation errors to the client
//         return response()->json(['errors' => $e->errors()], 422);
//     }

//     // Step 2: Authorization check
//     try {
//         // Check if the authenticated user owns the package linked to the trade
//         if ($trade->package->user_id !== auth()->id()) {
//             \Log::warning('Unauthorized access attempt during trade update.', [
//                 'user_id' => auth()->id(),
//                 'package_owner_id' => $trade->package->user_id,
//                 'trade_id' => $trade->id
//             ]);
//             return response()->json(['error' => 'Unauthorized access'], 403); // HTTP Status 403: Forbidden
//         }
//     } catch (Exception $e) {
//         \Log::error('Error during authorization check:', ['message' => $e->getMessage()]);
//         return response()->json(['error' => 'Authorization check failed', 'message' => $e->getMessage()], 500);
//     }

//     // Step 3: Update the trade
//     try {
//         // Update the trade with the validated data
//         $updated = $trade->update($validated);

//         // Check if the update was successful
//         if ($updated) {
//             \Log::info('Trade updated successfully.', ['trade_id' => $trade->id]);
//             return response()->json($trade, 200); // HTTP Status 200: Updated successfully
//         } else {
//             \Log::warning('Trade update failed without exception.', ['trade_id' => $trade->id]);
//             return response()->json(['error' => 'Failed to update trade'], 500);
//         }
//     } catch (Exception $e) {
//         // Log the error and return the error message
//         \Log::error('Trade update failed:', ['message' => $e->getMessage(), 'trade_id' => $trade->id]);
//         return response()->json(['error' => 'Failed to update trade', 'message' => $e->getMessage()], 500);
//     }
// }


// public function update(Request $request, Trade $trade): JsonResponse
// {
    
//     // Step 1: Validation
//     try {
//         // Validate the request input
//         $validated = $request->validate([
//             'trade_name' => 'required|string|max:255',
//             'market_pair_id' => 'required|exists:market_pairs,id',
//             'trade_type_id' => 'required|exists:trade_types,id',
//             'trade_date' => 'required|date',
//             'entry_price' => 'required|numeric',
//             'take_profit' => 'required|array|min:1|max:2', // Ensure it's an array with 1-2 elements
//             'take_profit.*' => 'required|numeric', // Validate each array element as numeric
//             'stop_loss' => 'required|numeric',
//             'time_frame' => 'required|string',
//             'validity' => 'required|string',
//             'status' => 'required|boolean',
//         ]);
//     } catch (\Illuminate\Validation\ValidationException $e) {
//         // Log validation errors
//         \Log::error('Validation failed during trade update:', $e->errors());

//         // Return validation errors to the client
//         return response()->json(['errors' => $e->errors()], 422);
//     }

//     // Step 2: Authorization check
//     try {
//         // Check if the authenticated user owns the package linked to the trade
//         if ($trade->package->user_id !== auth()->id()) {
//             \Log::warning('Unauthorized access attempt during trade update.', [
//                 'user_id' => auth()->id(),
//                 'package_owner_id' => $trade->package->user_id,
//                 'trade_id' => $trade->id
//             ]);
//             return response()->json(['error' => 'Unauthorized access'], 403); // HTTP Status 403: Forbidden
//         }
//     } catch (Exception $e) {
//         \Log::error('Error during authorization check:', ['message' => $e->getMessage()]);
//         return response()->json(['error' => 'Authorization check failed', 'message' => $e->getMessage()], 500);
//     }

//     // Step 3: Update the trade
//     try {
//         // Separate take_profit values
//         $takeProfit1 = $validated['take_profit'][0] ?? null; // First element or null
//         $takeProfit2 = $validated['take_profit'][1] ?? null; // Second element or null

//         // Prepare data for updating the trade
//         $tradeData = array_merge($validated, [
//             'take_profit' => $takeProfit1,
//             'take_profit_2' => $takeProfit2,
//         ]);

//         // Update the trade with the validated data
//         $updated = $trade->update($tradeData);

//         // Check if the update was successful
//         if ($updated) {
//             \Log::info('Trade updated successfully.', ['trade_id' => $trade->id]);
//             return response()->json($trade, 200); // HTTP Status 200: Updated successfully
//         } else {
//             \Log::warning('Trade update failed without exception.', ['trade_id' => $trade->id]);
//             return response()->json(['error' => 'Failed to update trade'], 500);
//         }
//     } catch (Exception $e) {
//         // Log the error and return the error message
//         \Log::error('Trade update failed:', ['message' => $e->getMessage(), 'trade_id' => $trade->id]);
//         return response()->json(['error' => 'Failed to update trade', 'message' => $e->getMessage()], 500);
//     }
// }


public function update(Request $request, Trade $trade): JsonResponse
{
    try {
        // Log the request data for debugging
        \Log::info('Request Data:', $request->all());

        // Validate the request
        $validated = $request->validate([
            'trade_name' => 'required|string|max:255',
            'market_pair_id' => 'required|exists:market_pairs,id',
            'trade_type_id' => 'required|exists:trade_types,id',
            'trade_date' => 'required|date',
            'entry_price' => 'required|numeric',
            'take_profit' => 'required|array|min:1|max:2',
            'take_profit.*' => 'required|numeric',
            'stop_loss' => 'required|numeric',
            'time_frame' => 'required|string',
            'validity' => 'required|string',
            'status' => 'required|boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

    

        

        // Access take_profit values
        $takeProfit1 = $validated['take_profit'][0] ?? null;
        $takeProfit2 = $validated['take_profit'][1] ?? null;

        // Step 2: Authorization check
        if ($trade->package->user_id !== auth()->id()) {
            \Log::warning('Unauthorized access attempt during trade update.', [
                'user_id' => auth()->id(),
                'package_owner_id' => $trade->package->user_id,
                'trade_id' => $trade->id
            ]);
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        // Step 3: Update the trade
        $tradeData = array_merge($validated, [
            'take_profit' => $takeProfit1,
            'take_profit_2' => $takeProfit2,
        ]);

        $trade->update($tradeData);

        // Step 4: Handle trade images
        if ($request->hasFile('images')) {
            $userId = auth()->id();
            $userFolder = "uploads/users/{$userId}/trades";

            // Ensure the directory exists
            if (!Storage::exists($userFolder)) {
                Storage::makeDirectory($userFolder);
            }

            // Delete old images from storage and database
            foreach ($trade->images as $oldImage) {
                if (Storage::exists('public/' . $oldImage->image_path)) {
                    Storage::delete('public/' . $oldImage->image_path);
                }
                $oldImage->delete();
            }

            // Save new images
            foreach ($request->file('images') as $image) {
                $path = $image->store($userFolder, 'public');

                // Save image details in the database
                $trade->images()->create([
                    'image_path' => $path,
                    'image_name' => $image->getClientOriginalName(),
                ]);
            }
        }

        \Log::info('Trade updated successfully with images.', ['trade_id' => $trade->id]);
        return response()->json($trade->load('images'), 200);
    } catch (Exception $e) {
        \Log::error('Trade update failed:', ['message' => $e->getMessage(), 'trade_id' => $trade->id]);
        return response()->json(['error' => 'Failed to update trade', 'message' => $e->getMessage()], 500);
    }
}






    /**
     * Remove the specified trade from the database.
     *
     * @param Trade $trade
     * @return JsonResponse
     */
    public function destroy(Trade $trade): JsonResponse
    {
        try {
            // Check if the trade's package belongs to the authenticated user
            if ($trade->package->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized access'], 403); // HTTP Status 403: Forbidden
            }

            // Delete the trade
            $trade->delete();
            return response()->json(['message' => 'Trade deleted successfully'], 200); // HTTP Status 200: Deleted
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete trade', 'message' => $e->getMessage()], 500);
        }
    }


        public function getAllTrades()
        {
                // Retrieve the current authenticated user's ID (or from query parameter if needed)
                $userId =  auth()->id();
            
                // Eager load related models for better performance
                $trades = Trade::with([
                    'package.user' => function ($query) {
                        $query->with([
                            'profile' => function ($profileQuery) {
                                $profileQuery->with([
                                    'badge',          // Trader's badge
                                    'country',        // Trader's country
                                    'city',           // Trader's city
                                    'languages',      // Trader's languages
                                ]);
                            }
                        ]);
                    },
                    'marketPair',       // MarketPair relationship for Trade
                    'tradeType',        // TradeType relationship for Trade
                    'signalPerformance' // Eager load all signal performances for each trade
                ])->get()->map(function ($trade) use ($userId) {
                    // Check if the trade has an associated package and if the package has orders for the user
                    $hasPackage = $trade->package && $trade->package->orders;
            
                    // Check if the user has purchased the relevant package
                    $isUserInPackage = false;
            
                    // Loop through orders to find if the user has purchased this package
                    if ($hasPackage) {
                        $isUserInPackage = $trade->package->orders->where('user_id', $userId)->isNotEmpty();
                    }
            
                    // Conditionally set trade details based on package purchase
                    if (!$isUserInPackage) {
                        // Show stars for all other users who haven't purchased the package
                        $trade->take_profit = '***';
                        $trade->stop_loss = '***';
                        $trade->entry_price = '***';
                        $trade->take_profit_2 = '***';
                    } else {
                        
                        
                    }
            
                    // Check if the user has followed this trade
                    $trade->is_followed = $trade->signalPerformance->where('user_id', $userId)->isNotEmpty();
                    
                     // Add image URLs to the trade
                    $trade->images = $trade->images->map(function ($image) {
                        return [
                            'image_name' => $image->image_name,
                            'image_url' => $image->picture_url, // This will return the full URL using the accessor
                       
                        ];
                    });
                    return $trade;
                });

                
            
                return response()->json([
                    'status' => true,
                    'message' => 'Trades and related data retrieved successfully',
                    'data' => [
                        'trades' => $trades
                    ]
                ], 200);
        }



        public function getAllTradesguest()
        {
                // Retrieve the current authenticated user's ID (or from query parameter if needed)
                $userId =  0;
            
                // Eager load related models for better performance
                $trades = Trade::with([
                    'package.user' => function ($query) {
                        $query->with([
                            'profile' => function ($profileQuery) {
                                $profileQuery->with([
                                    'badge',          // Trader's badge
                                    'country',        // Trader's country
                                    'city',           // Trader's city
                                    'languages',      // Trader's languages
                                ]);
                            }
                        ]);
                    },
                    'marketPair',       // MarketPair relationship for Trade
                    'tradeType',        // TradeType relationship for Trade
                    'signalPerformance' // Eager load all signal performances for each trade
                ])->get()->map(function ($trade) use ($userId) {
                    // Check if the trade has an associated package and if the package has orders for the user
                    $hasPackage = $trade->package && $trade->package->orders;
            
                    // Check if the user has purchased the relevant package
                    $isUserInPackage = false;
            
                    // Loop through orders to find if the user has purchased this package
                    if ($hasPackage) {
                        $isUserInPackage = $trade->package->orders->where('user_id', $userId)->isNotEmpty();
                    }
            
                    // Conditionally set trade details based on package purchase
                    if (!$isUserInPackage) {
                        // Show stars for all other users who haven't purchased the package
                        $trade->take_profit = '***';
                        $trade->stop_loss = '***';
                        $trade->entry_price = '***';
                        $trade->take_profit_2 = '***';
                    } else {
                        
                        
                    }
            
                    // Check if the user has followed this trade
                    $trade->is_followed = $trade->signalPerformance->where('user_id', $userId)->isNotEmpty();
                    
                     // Add image URLs to the trade
                     $trade->images = [ ];
                   

                    return $trade;
                });
            
                return response()->json([
                    'status' => true,
                    'message' => 'Trades and related data retrieved successfully',
                    'data' => [
                        'trades' => $trades
                    ]
                ], 200);
        }
        
    

    
    
    

}
