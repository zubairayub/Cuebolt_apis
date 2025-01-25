<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Commission;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class OrdersController extends Controller
{
    /**
     * Display a listing of the user's orders.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            // Step 1: Ensure the user is authenticated
            if (!auth()->check()) {
                return response()->json(['error' => 'Unauthorized access'], 401);
            }

            // Step 2: Fetch all orders that belong to the authenticated user
            $orders = Order::where('user_id', auth()->id()) // Only fetch orders where the user_id matches the authenticated user's ID
                ->with(['package', 'paymentMethod', 'orderStatus', 'package.user']) // Load related data
                ->paginate(10); // Paginate the results

            // Step 3: Return the orders with the related data
            return response()->json($orders, 200);

        } catch (Exception $e) {
            // Handle any unexpected errors
            return response()->json(['error' => 'Failed to fetch orders', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created order in the database.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        // Step 1: Validation
        try {
            $validated = $request->validate([
                'package_id' => 'required|exists:packages,id',
                'payment_method_id' => 'required|exists:payment_methods,id',
                'order_status_id' => 'required|exists:order_statuses,id',
                'amount' => 'required|numeric',
                'expiry_date' => 'required|date',
                'auto_renew' => 'boolean',
                'commission_id' => 'required|exists:commissions,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log validation errors and return to the client
            \Log::error('Validation failed during order creation:', $e->errors());
            return response()->json(['errors' => $e->errors()], 422);
        }

        // Step 2: Authorization check - Ensure user is authenticated
        $user = auth()->user(); // Retrieve the authenticated user

        if (!$user) {
            return response()->json(['error' => 'User is not authenticated.'], 401); // If not authenticated
        }

        // Ensure that the user exists in the 'users' table
        if (!User::find($user->id)) {
            return response()->json(['error' => 'User not found in the database.'], 404);
        }

        // Check if the user is authorized to create an order for this package
        try {
            $package = Package::findOrFail($validated['package_id']);

            // Example of package ownership check (if needed)
            // if ($package->user_id !== $user->id) {
            //     return response()->json(['error' => 'Unauthorized to create order for this package'], 403);
            // }

        } catch (Exception $e) {
            return response()->json(['error' => 'Package not found or authorization failed.', 'message' => $e->getMessage()], 500);
        }

        // Step 3: Create the order
        try {
            // Attach the authenticated user to the order
            $validated['user_id'] = $user->id;  // Add the user ID to the validated data

            // Step 4: Get the package associated with the order
            $package = Package::find($validated['package_id']);

            // Step 5: Ensure the user is purchasing a package from a different trader
            if ($package && $package->user_id !== $user->id) {


                // Check if the user has already purchased any valid, non-expired package from this trader
                $existingOrder = Order::where('package_id', $package->id)
                    ->where('user_id', $user->id)
                    ->where('expiry_date', '>', Carbon::now())  // Ensure it's not expired
                    ->first();

                // If the user already has a non-expired order for this trader, skip incrementing
                if (!$existingOrder) {
                    // Step 6: If no valid order exists, increment the `users_count` for the trader's profile
                    $profile = UserProfile::where('user_id', $package->user_id)->first();

                    if ($profile) {
                        // Increment the `users_count` by 1 for the trader (package owner)
                        $profile->increment('users_count');
                    }
                    //  return response()->json(['message' => 'Order already exists for this trader with a valid package.'], 200);
                }


            }

            // Fetch the commission percentage for the order using commission_id
            $commission = Commission::find($validated['commission_id']); // Ensure this is a valid commission object

            if ($commission) {
                // Calculate the commission amount
                $commissionAmount = ($validated['amount'] * $commission->percentage) / 100;

                // Subtract commission from amount
                $amountAfterCommission = $validated['amount'] - $commissionAmount;

                // Add commission data to the validated data
                
                $validated['commission_amount'] = $commissionAmount;
                $validated['amount_after_commission'] = $amountAfterCommission;
                $validated['stripe_subscription_id'] = $request->stripe_subscription_id;
            }

            $order = Order::create($validated);



            return response()->json($order, 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to create order', 'message' => $e->getMessage()], 500);
        }
    }


    /**
     * Display the specified order.
     *
     * @param Order $order
     * @return JsonResponse
     */
    public function show(Order $order): JsonResponse
    {
        try {
            // Step 1: Ensure the user is authenticated
            if (!auth()->check()) {
                return response()->json(['error' => 'Unauthorized access'], 401);
            }

            // Step 2: Check if the authenticated user is the owner of the order
            if ($order->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized access to this order'], 403);
            }

            // Step 3: Fetch the related data from the tables
            $orderDetails = $order->load([
                'package',  // Load the related package details
                'paymentMethod',  // Load the related payment method details
                'orderStatus',  // Load the related order status details
                'package.user'  // Load the trader's (package creator's) details from the users table
            ]);

            // Step 4: Return the order details along with related data
            return response()->json($orderDetails, 200);

        } catch (ModelNotFoundException $e) {
            // Handle case where the order is not found
            return response()->json(['error' => 'Order not found', 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            // Handle other unexpected errors
            return response()->json(['error' => 'Failed to fetch order', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified order.
     *
     * @param Request $request
     * @param Order $order
     * @return JsonResponse
     */
    public function update(Request $request, Order $order): JsonResponse
    {
        try {
            // Step 1: Validate the input fields
            $validated = $request->validate([
                'package_id' => 'sometimes|exists:packages,id',
                'payment_method_id' => 'sometimes|exists:payment_methods,id',
                'order_status_id' => 'sometimes|exists:order_statuses,id',
                'amount' => 'sometimes|numeric',
                'expiry_date' => 'sometimes|date',
                'auto_renew' => 'sometimes|boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        try {
            // Step 2: Ensure the user is authenticated
            if (!auth()->check()) {
                return response()->json(['error' => 'Unauthorized access'], 401);
            }

            // Step 3: Ensure the order belongs to the authenticated user
            if ($order->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized access'], 403);
            }

            // Step 4: Update the order with the validated data
            $order->update($validated);

            // Step 5: Return the updated order
            return response()->json($order, 200);

        } catch (Exception $e) {
            // Step 6: Handle any unexpected errors
            return response()->json(['error' => 'Failed to update order', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified order from the database.
     *
     * @param Order $order
     * @return JsonResponse
     */
    public function destroy(Order $order): JsonResponse
    {
        try {
            // Step 1: Ensure the user is authenticated
            if (!auth()->check()) {
                return response()->json(['error' => 'Unauthorized access'], 401);
            }

            // Step 2: Ensure the order belongs to the authenticated user
            if ($order->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized access'], 403);
            }

            // Step 3: Delete the order
            $order->delete();

            // Step 4: Return a success message
            return response()->json(['message' => 'Order deleted successfully'], 200);

        } catch (Exception $e) {
            // Step 5: Handle unexpected errors
            return response()->json(['error' => 'Failed to delete order', 'message' => $e->getMessage()], 500);
        }
    }
}
