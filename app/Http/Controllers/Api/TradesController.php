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
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log validation errors
            \Log::error('Validation failed during trade creation:', $e->errors());
    
            // Return validation errors to the client
            return response()->json(['errors' => $e->errors()], 422);
        }
    
        // Step 2: Authorization check
        try {
            // Check if the package belongs to the authenticated user
            $package = Package::findOrFail($validated['package_id']);
            if ($package->user_id !== auth()->id()) {
                \Log::warning('Unauthorized access attempt during trade creation.', [
                    'user_id' => auth()->id(),
                    'package_owner_id' => $package->user_id,
                ]);
                return response()->json(['error' => 'Unauthorized to create trade for this package'], 403); // HTTP Status 403: Forbidden
            }
        } catch (Exception $e) {
            \Log::error('Error during authorization check:', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Authorization check failed', 'message' => $e->getMessage()], 500);
        }
    
        // Step 3: Create the trade
        try {
            $takeProfit1 = $validated['take_profit'][0] ?? null; // First element or null
            $takeProfit2 = $validated['take_profit'][1] ?? null; // Second element or null
            // Prepare data for trade creation
            $tradeData = array_merge($validated, [
                'take_profit' => $takeProfit1,
                'take_profit_2' => $takeProfit2,
            ]);
            // Create the trade using the validated data
            $trade = Trade::create($tradeData);
    
            \Log::info('Trade created successfully.', ['trade_id' => $trade->id]);
            return response()->json($trade, 201); // HTTP Status 201: Created
        } catch (Exception $e) {
            // Log the error and return the error message
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

    /**
     * Update the specified trade in the database.
     *
     * @param Request $request
     * @param Trade $trade
     * @return JsonResponse
     */
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


public function update(Request $request, Trade $trade): JsonResponse
{
    
    // Step 1: Validation
    try {
        // Validate the request input
        $validated = $request->validate([
            'trade_name' => 'required|string|max:255',
            'market_pair_id' => 'required|exists:market_pairs,id',
            'trade_type_id' => 'required|exists:trade_types,id',
            'trade_date' => 'required|date',
            'entry_price' => 'required|numeric',
            'take_profit' => 'required|array|min:1|max:2', // Ensure it's an array with 1-2 elements
            'take_profit.*' => 'required|numeric', // Validate each array element as numeric
            'stop_loss' => 'required|numeric',
            'time_frame' => 'required|string',
            'validity' => 'required|string',
            'status' => 'required|boolean',
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        // Log validation errors
        \Log::error('Validation failed during trade update:', $e->errors());

        // Return validation errors to the client
        return response()->json(['errors' => $e->errors()], 422);
    }

    // Step 2: Authorization check
    try {
        // Check if the authenticated user owns the package linked to the trade
        if ($trade->package->user_id !== auth()->id()) {
            \Log::warning('Unauthorized access attempt during trade update.', [
                'user_id' => auth()->id(),
                'package_owner_id' => $trade->package->user_id,
                'trade_id' => $trade->id
            ]);
            return response()->json(['error' => 'Unauthorized access'], 403); // HTTP Status 403: Forbidden
        }
    } catch (Exception $e) {
        \Log::error('Error during authorization check:', ['message' => $e->getMessage()]);
        return response()->json(['error' => 'Authorization check failed', 'message' => $e->getMessage()], 500);
    }

    // Step 3: Update the trade
    try {
        // Separate take_profit values
        $takeProfit1 = $validated['take_profit'][0] ?? null; // First element or null
        $takeProfit2 = $validated['take_profit'][1] ?? null; // Second element or null

        // Prepare data for updating the trade
        $tradeData = array_merge($validated, [
            'take_profit' => $takeProfit1,
            'take_profit_2' => $takeProfit2,
        ]);

        // Update the trade with the validated data
        $updated = $trade->update($tradeData);

        // Check if the update was successful
        if ($updated) {
            \Log::info('Trade updated successfully.', ['trade_id' => $trade->id]);
            return response()->json($trade, 200); // HTTP Status 200: Updated successfully
        } else {
            \Log::warning('Trade update failed without exception.', ['trade_id' => $trade->id]);
            return response()->json(['error' => 'Failed to update trade'], 500);
        }
    } catch (Exception $e) {
        // Log the error and return the error message
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
            'tradeType'         // TradeType relationship for Trade
        ])->get();

        return response()->json([
            'status' => true,
            'message' => 'Trades and related data retrieved successfully',
            'data' => [
                'trades' => $trades
            ]
        ], 200);
    }

}
