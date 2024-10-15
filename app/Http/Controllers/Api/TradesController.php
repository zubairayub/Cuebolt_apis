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
    public function index(): JsonResponse
    {
        try {
                // Get all package IDs associated with the authenticated user
            $packageIds = Package::where('user_id', auth()->id())->pluck('id');

            // Fetch all trades that are linked to the user's packages
            $trades = Trade::whereIn('package_id', $packageIds)->paginate(10);
            return response()->json($trades, 200); // HTTP Status 200
        } catch (Exception $e) {
            // Return a structured error message in case of any failure
            return response()->json(['error' => 'Failed to fetch trades', 'message' => $e->getMessage()], 500);
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
                'take_profit' => 'required|numeric',
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
            // Create the trade using the validated data
            $trade = Trade::create($validated);
    
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
            'take_profit' => 'required|numeric',
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
        // Update the trade with the validated data
        $updated = $trade->update($validated);

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
}
