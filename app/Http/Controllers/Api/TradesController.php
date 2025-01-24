<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trade;
use App\Models\TradeType;
use App\Models\MarketPair;
use App\Models\User;
use App\Models\Order;
use App\Models\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\TradeJournal;
use App\Models\Emotion;
use Illuminate\Support\Facades\Log;


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
    //             'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Image validation
    //         ]);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         Log::error('Validation failed during trade creation:', $e->errors());
    //         return response()->json(['errors' => $e->errors()], 422);
    //     }

    //     // Step 2: Authorization check
    //     try {
    //         $package = Package::findOrFail($validated['package_id']);
    //         if ($package->user_id !== auth()->id()) {
    //             Log::warning('Unauthorized access attempt during trade creation.', [
    //                 'user_id' => auth()->id(),
    //                 'package_owner_id' => $package->user_id,
    //             ]);
    //             return response()->json(['error' => 'Unauthorized to create trade for this package'], 403);
    //         }
    //     } catch (Exception $e) {
    //         Log::error('Error during authorization check:', ['message' => $e->getMessage()]);
    //         return response()->json(['error' => 'Authorization check failed', 'message' => $e->getMessage()], 500);
    //     }

    //     // Step 3: Create the trade
    //     try {
    //         $takeProfit1 = $validated['take_profit'][0] ?? null;
    //         $takeProfit2 = $validated['take_profit'][1] ?? null;

    //         // Prepare data for trade creation
    //         $tradeData = array_merge($validated, [
    //             'take_profit' => $takeProfit1,
    //             'take_profit_2' => $takeProfit2,
    //         ]);

    //         // Create the trade using the validated data
    //         $trade = Trade::create($tradeData);

    //         // Step 4: Save Images (if any)
    //         if ($request->hasFile('images')) {
    //             $userId = auth()->id(); // Get the authenticated user's ID

    //             // Define the folder path
    //             $basePath = "uploads/users/{$userId}/trades";

    //             // Ensure the directory exists
    //             if (!Storage::exists($basePath)) {
    //                 Storage::makeDirectory($basePath); // Creates the directory if it doesn't exist
    //             }

    //             foreach ($request->file('images') as $image) {
    //                 // Store the file in the user's trades folder
    //                 $filePath = $image->store($basePath, 'public');

    //                 // Optional: Save image details in the database
    //                 $trade->images()->create([
    //                     'image_path' => $filePath,
    //                     'image_name' => $image->getClientOriginalName(),
    //                 ]);
    //             }

    //             // return response()->json(['message' => 'Images uploaded successfully.'], 201);
    //         } else {
    //             Log::info('Trade Created without images.', ['trade_id' => $trade->id]);
    //         }

    //         //notification
    //         // Step 1: Get FCM tokens for users who ordered this package with valid expiry and status
    //         $tokens = User::whereIn('id', Order::where('package_id', $validated['package_id'])
    //             ->where('order_status_id', 2) // Only orders with status 2
    //             ->where('expiry_date', '>=', now()) // Ensure expiry date hasn't passed
    //             ->pluck('user_id'))
    //             ->whereNotNull('fcm_token')
    //             ->pluck('fcm_token'); // Get all the FCM tokens

    //         // Step 2: Get package details
    //         $package = Package::find($validated['package_id']);
    //         $packageName = $package->name ?? 'Unknown Package';

    //         // Step 3: Get package owner's username
    //         $ownerUsername = User::find($package->user_id)->username ?? 'Unknown Owner';

    //         // Step 4: Get the market pair name from the market_pairs table
    //         $marketPair = MarketPair::find($validated['market_pair_id']);
    //         $marketPairName = $marketPair ? "{$marketPair->base_currency}/{$marketPair->quote_currency}" : 'Unknown Market Pair';

    //         // Step 5: Get the trade type name from the trade_types table
    //         $tradeType = TradeType::find($validated['trade_type_id']);
    //         $tradeTypeName = $tradeType ? $tradeType->name : 'Unknown Trade Type';

    //         // Step 6: Prepare the notification title
    //         $title = "{$ownerUsername} shared a new signal with trade details";

    //         // Step 7: Format the notification body for a user-friendly presentation

    //         $body = "A new trade has been created for the package '{$packageName}' you ordered.\n\n";
    //         $body .= "📈 Market Pair: {$marketPairName}\n";
    //         $body .= "🔹 Trade Type: {$tradeTypeName}\n";
    //         $body .= "💰 Entry Price: {$validated['entry_price']}\n";
    //         $body .= "📊 Take Profit: ";

    //         if ($takeProfit1 && $takeProfit2) {
    //             // Show both take profit values
    //             $body .= "{$takeProfit1} / {$takeProfit2}\n";
    //         } elseif ($takeProfit1) {
    //             // Show only the first take profit value if the second is not set
    //             $body .= "{$takeProfit1}\n";
    //         } else {
    //             // Show the second take profit value if the first is not set
    //             $body .= "{$takeProfit2}\n";
    //         }

    //         $body .= "📉 Stop Loss: {$validated['stop_loss']}\n";


    //         // Step 8: Send notification if there are valid tokens
    //         if ($tokens->isNotEmpty()) {
    //             send_push_notification(
    //                 $tokens->toArray(),
    //                 $title,
    //                 $body,
    //                 [
    //                     'trade_id' => $trade->id,
    //                     'package_id' => $validated['package_id'],
    //                     'market_pair_id' => $validated['market_pair_id'],
    //                     'trade_type_id' => $validated['trade_type_id'],
    //                     'entry_price' => $validated['entry_price'],
    //                     'take_profit' => $validated['take_profit'],
    //                     'stop_loss' => $validated['stop_loss'],
    //                     'type' => 'trade_notification'
    //                 ],
    //                 'trade_notification'
    //             );
    //         }


    //         //end notification


    //         Log::info('Trade created successfully with images.', ['trade_id' => $trade->id]);
    //         return response()->json($trade->load('images'), 201); // Include images in the response
    //     } catch (Exception $e) {
    //         Log::error('Trade creation failed:', ['message' => $e->getMessage()]);
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
            Log::error('Validation failed during trade creation:', $e->errors());
            return response()->json(['errors' => $e->errors()], 422);
        }

        // Step 2: Authorization check
        try {
            $package = Package::findOrFail($validated['package_id']);
            if ($package->user_id !== auth()->id()) {
                Log::warning('Unauthorized access attempt during trade creation.', [
                    'user_id' => auth()->id(),
                    'package_owner_id' => $package->user_id,
                ]);
                return response()->json(['error' => 'Unauthorized to create trade for this package'], 403);
            }
        } catch (Exception $e) {
            Log::error('Error during authorization check:', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Authorization check failed', 'message' => $e->getMessage()], 500);
        }

        // Step 3: Create the trade
        try {
            // Prepare data for trade creation
            $takeProfit1 = $validated['take_profit'][0] ?? null;
            $takeProfit2 = $validated['take_profit'][1] ?? null;

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
            } else {
                Log::info('Trade Created without images.', ['trade_id' => $trade->id]);
            }

            // Step 5: Send Push Notifications
            $tokens = User::whereIn('id', Order::where('package_id', $validated['package_id'])
                ->where('order_status_id', 2) // Only orders with status 2
                ->where('expiry_date', '>=', now()) // Ensure expiry date hasn't passed
                ->pluck('user_id'))
                ->whereNotNull('fcm_token')
                ->pluck('fcm_token'); // Get all the FCM tokens

            $userIds = Order::where('package_id', $validated['package_id'])
                ->where('order_status_id', 2) // Only orders with status 2
                ->where('expiry_date', '>=', now()) // Ensure expiry date hasn't passed
                ->pluck('user_id'); // Get user IDs
            //updated notification
           // Step 2: Fetch users with valid FCM tokens and their IDs
            $usersWithTokens = User::whereIn('id', $userIds)
                ->whereNotNull('fcm_token') // Only users with valid FCM tokens
                ->get(['id', 'fcm_token']); // Fetch both user ID and FCM token

            // Step 3: Extract tokens and user IDs separately if needed
            $tokens = $usersWithTokens->pluck('fcm_token');
            $validUserIds = $usersWithTokens->pluck('id');
            // dd($tokens);
            if ($tokens->isNotEmpty()) {
                
                // Step 1: Prepare button data for the notification
                $action = 'follow_trade'; // Action name
                $label = 'Follow Trade';  // Button label
                $url = url("/trade/{$trade->id}"); // Link to the trade details
                // Prepare the notification body
                $package = Package::find($validated['package_id']);
                $marketPair = MarketPair::find($validated['market_pair_id']);
                $tradeType = TradeType::find($validated['trade_type_id']);
                $ownerUsername = User::find($package->user_id)->username ?? 'Unknown Owner';
                $marketPairName = $marketPair ? "{$marketPair->base_currency}/{$marketPair->quote_currency}" : 'Unknown Market Pair';
                $tradeTypeName = $tradeType ? $tradeType->name : 'Unknown Trade Type';

                $title = "{$ownerUsername} shared a new signal";

                // Prepare the trade details for the notification
                $body = "A new trade has been created for the package '{$package->name}'.\n\n";
                $body .= "📈 Market Pair: {$marketPairName}\n";
                $body .= "🔹 Trade Type: {$tradeTypeName}\n";
                $body .= "💰 Entry Price: {$validated['entry_price']}\n";
                $body .= "📊 Take Profit: " . implode(" / ", $validated['take_profit']) . "\n"; // Use implode to join array values
                $body .= "📉 Stop Loss: {$validated['stop_loss']}\n";
                $takeProfitString = is_array($validated['take_profit']) ? implode(" / ", array_map('strval', $validated['take_profit'])) : $validated['take_profit'];
               // dd($tokens);
                send_push_notification(
                    $tokens->toArray(),
                    $title,
                    $body,
                    [
                        'trade_id' => $trade->id,
                        'package_id' => $validated['package_id'],
                        'market_pair_id' => $validated['market_pair_id'],
                        'trade_type_id' => $validated['trade_type_id'],
                        'entry_price' => $validated['entry_price'],
                        'take_profit' => $takeProfitString,
                        'stop_loss' => $validated['stop_loss'],
                        'type' => 'trade_notification',
                        'action' => $action,  // Pass the action name
                        'label' => $label,    // Pass the button label
                        'url' => $url         // Pass the URL link for the button
                    ],
                    type: 'trade_notification',
                    userIds: $validUserIds->toArray(),
                );
            }
            //end 



              //notification
            // Step 1: Get FCM tokens for users who ordered this package with valid expiry and status
            // $tokens = User::whereIn('id', Order::where('package_id', $validated['package_id'])
            //     ->where('order_status_id', 2) // Only orders with status 2
            //     ->where('expiry_date', '>=', now()) // Ensure expiry date hasn't passed
            //     ->pluck('user_id'))
            //     ->whereNotNull('fcm_token')
            //     ->pluck('fcm_token'); // Get all the FCM tokens

            // // Step 2: Get package details
            // $package = Package::find($validated['package_id']);
            // $packageName = $package->name ?? 'Unknown Package';

            // // Step 3: Get package owner's username
            // $ownerUsername = User::find($package->user_id)->username ?? 'Unknown Owner';

            // // Step 4: Get the market pair name from the market_pairs table
            // $marketPair = MarketPair::find($validated['market_pair_id']);
            // $marketPairName = $marketPair ? "{$marketPair->base_currency}/{$marketPair->quote_currency}" : 'Unknown Market Pair';

            // // Step 5: Get the trade type name from the trade_types table
            // $tradeType = TradeType::find($validated['trade_type_id']);
            // $tradeTypeName = $tradeType ? $tradeType->name : 'Unknown Trade Type';

            // // Step 6: Prepare the notification title
            // $title = "{$ownerUsername} shared a new signal with trade details";

            // // Step 7: Format the notification body for a user-friendly presentation

            // $body = "A new trade has been created for the package '{$packageName}' you ordered.\n\n";
            // $body .= "📈 Market Pair: {$marketPairName}\n";
            // $body .= "🔹 Trade Type: {$tradeTypeName}\n";
            // $body .= "💰 Entry Price: {$validated['entry_price']}\n";
            // $body .= "📊 Take Profit: ";

            // if ($takeProfit1 && $takeProfit2) {
            //     // Show both take profit values
            //     $body .= "{$takeProfit1} / {$takeProfit2}\n";
            // } elseif ($takeProfit1) {
            //     // Show only the first take profit value if the second is not set
            //     $body .= "{$takeProfit1}\n";
            // } else {
            //     // Show the second take profit value if the first is not set
            //     $body .= "{$takeProfit2}\n";
            // }

            // $body .= "📉 Stop Loss: {$validated['stop_loss']}\n";


            // // Step 8: Send notification if there are valid tokens
            // if ($tokens->isNotEmpty()) {
            //     send_push_notification(
            //         $tokens->toArray(),
            //         $title,
            //         $body,
            //         [
            //             'trade_id' => $trade->id,
            //             'package_id' => $validated['package_id'],
            //             'market_pair_id' => $validated['market_pair_id'],
            //             'trade_type_id' => $validated['trade_type_id'],
            //             'entry_price' => $validated['entry_price'],
            //             'take_profit' => $validated['take_profit'],
            //             'stop_loss' => $validated['stop_loss'],
            //             'type' => 'trade_notification'
            //         ],
            //         'trade_notification'
            //     );
            // }


            //end notification


            Log::info('Trade created successfully with images.', ['trade_id' => $trade->id]);
            return response()->json($trade->load('images'), 201); // Include images in the response
        } catch (Exception $e) {
            Log::error('Trade creation failed:', ['message' => $e->getMessage()]);
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
            Log::info('Request Data:', $request->all());

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
                Log::warning('Unauthorized access attempt during trade update.', [
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

            Log::info('Trade updated successfully with images.', ['trade_id' => $trade->id]);
            return response()->json($trade->load('images'), 200);
        } catch (Exception $e) {
            Log::error('Trade update failed:', ['message' => $e->getMessage(), 'trade_id' => $trade->id]);
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
        $userId = auth()->id();

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
            'signalPerformance', // Eager load all signal performances for each trade
            'tradeJournal',     // Eager load TradeJournal relationship (added this line)
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

            // Add journal data to the trade (added journal data)
            if ($trade->tradeJournal) {
                $trade->trade_journal = [
                    'trade_decision' => $trade->tradeJournal->trade_decision,
                    'trade_analysis' => $trade->tradeJournal->trade_analysis,
                    'trade_reflection' => $trade->tradeJournal->trade_reflection,
                    'trade_improvement' => $trade->tradeJournal->trade_improvement,
                    'emotion' => $trade->tradeJournal->emotion ? $trade->tradeJournal->emotion->emotion_name : null, // Emotion related to the journal
                ];
            }
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
        $userId = 0;

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
            $trade->trade_journal = [];
            // Add image URLs to the trade
            $trade->images = [];


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




    public function store_trade_journal(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'trade_id' => 'required|exists:trades,id', // Ensure the trade exists
            'emotion_id' => 'nullable|exists:trade_emotions,id', // Ensure the emotion exists (nullable)
            'trade_decision' => 'required|string|max:255',
            'trade_reflection' => 'nullable|string',
            'trade_improvement' => 'nullable|string',
            'trade_strategy' => 'nullable|string',
            'trade_risk_management' => 'nullable|string',
            'trade_analysis' => 'nullable|string',

        ]);

        // Create a new trade journal entry
        $tradeJournal = TradeJournal::create([
            'trade_id' => $request->trade_id,
            'emotion_id' => $request->emotion_id,
            'trade_decision' => $request->trade_decision,
            'trade_reflection' => $request->trade_reflection,
            'trade_improvement' => $request->trade_improvement,
            'trade_strategy' => $request->trade_strategy,
            'trade_risk_management' => $request->trade_risk_management,
            'trade_analysis' => $request->trade_analysis,
        ]);

        // Return a success response
        return response()->json([
            'status' => true,
            'message' => 'Trade journal entry created successfully.',
            'data' => $tradeJournal,
        ], 201);
    }

    public function update_trade_journal(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'id' => 'required|exists:trade_journals,id', // Ensure the trade journal exists
            'trade_id' => 'required|exists:trades,id', // Ensure the trade exists
            'emotion_id' => 'nullable|exists:trade_emotions,id', // Ensure the emotion exists (nullable)
            'trade_decision' => 'required|string|max:255',
            'trade_reflection' => 'nullable|string',
            'trade_improvement' => 'nullable|string',
            'trade_strategy' => 'nullable|string',
            'trade_risk_management' => 'nullable|string',
            'trade_analysis' => 'nullable|string',
        ]);

        // Find the trade journal entry by ID
        $tradeJournal = TradeJournal::findOrFail($request->id);

        // Update the trade journal entry with the new data
        $tradeJournal->update([
            'trade_id' => $request->trade_id,
            'emotion_id' => $request->emotion_id,
            'trade_decision' => $request->trade_decision,
            'trade_reflection' => $request->trade_reflection,
            'trade_improvement' => $request->trade_improvement,
            'trade_strategy' => $request->trade_strategy,
            'trade_risk_management' => $request->trade_risk_management,
            'trade_analysis' => $request->trade_analysis,
        ]);

        // Return a success response
        return response()->json([
            'status' => true,
            'message' => 'Trade journal entry updated successfully.',
            'data' => $tradeJournal,
        ], 200);
    }









}
