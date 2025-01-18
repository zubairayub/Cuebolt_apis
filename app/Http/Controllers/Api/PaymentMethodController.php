<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\UserPaymentDetail;
use App\Models\Bank;
use App\Models\Wallet;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Cryptocurrency;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class PaymentMethodController extends Controller
{
    // Fetch all payment methods
    public function getPaymentMethods()
    {
        return response()->json(PaymentMethod::all());
    }

    public function getBanks()
    {
        return response()->json(Bank::all());
    }

    public function getWallets()
    {
        return response()->json(Wallet::all());
    }

    public function getCryptocurrencies()
    {
        return response()->json(Cryptocurrency::all());
    }

    public function storeUserPaymentDetails(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication failed. Please provide a valid token.',
                ], 401);
            }
            $validated = $request->validate([
                'payment_method_id' => 'required|exists:payment_methods,id',
                'details' => 'required|array', // Dynamic fields as JSON
            ]);

            // Process and save the payment details
            $paymentDetail = UserPaymentDetail::create([
                'user_id' => $user->id,
                'payment_method_id' => $validated['payment_method_id'],
                'details' => json_encode($validated['details']), // Save as JSON
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment details saved successfully.',
                'data' => $paymentDetail,
            ], 201);

        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Handle other unexpected errors
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllPaymentMethods(Request $request)
    {
        try {
            // Fetch all payment methods
            $paymentMethods = PaymentMethod::with('userPaymentDetails')->get();

            // Transform the data for API response
            $result = $paymentMethods->map(function ($method) {
                return [
                    'id' => $method->id,
                    'name' => $method->method_name,
                    'details' => $method->userPaymentDetails->map(function ($detail) {
                        return [
                            'id' => $detail->id,
                            'user_id' => $detail->user_id,
                            'details' => json_decode($detail->details), // Parse JSON details
                            'created_at' => $detail->created_at->toDateTimeString(),
                        ];
                    }),
                ];
            });

            // Return consolidated payment methods
            return response()->json([
                'success' => true,
                'message' => 'Payment methods retrieved successfully.',
                'data' => $result,
            ], 200);

        } catch (\Exception $e) {
            // Handle unexpected errors
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment methods.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllMethods(Request $request)
    {
        try {
            // Fetch data from all tables
$paymentMethods = PaymentMethod::all(['id', 'method_name']);
$wallets = Wallet::all();
$banks = Bank::all();
$cryptocurrencies = Cryptocurrency::all();

// Combine and format the results
$result = collect([
    [
        'type' => 'payment_methods',
        'methods' => $paymentMethods->map(function ($method) {
            return [
                'id' => $method->id,
                'name' => $method->method_name,
            ];
        }),
    ],
    [
        'type' => 'wallets',
        'methods' => $wallets->map(function ($wallet) {
            return [
                'id' => $wallet->id,
                'name' => $wallet->name,
            ];
        }),
    ],
    [
        'type' => 'banks',
        'methods' => $banks->map(function ($bank) {
            return [
                'id' => $bank->id,
                'name' => $bank->name,
                'code' => $bank->code,
            ];
        }),
    ],
    [
        'type' => 'cryptocurrencies',
        'methods' => $cryptocurrencies->map(function ($crypto) {
            return [
                'id' => $crypto->id,
                'name' => $crypto->name,
                'network' => $crypto->network,
            ];
        }),
    ],
]);


            // Return the payment methods
            return response()->json([
                'success' => true,
                'message' => 'Payment methods retrieved successfully.',
                'data' => $result,
            ], 200);

        } catch (\Exception $e) {
            // Handle unexpected errors
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment methods.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function requestPayment(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        try {
            // Get the authenticated user from Bearer token
            $user = auth()->user();  // This will return the currently authenticated user

            // If you need to access user id, you can use $user->id
            // Calculate the total amount the trader has earned
            if (!$user) {
                // Handle error if user is not authenticated
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            $traderId = $user->id;
            // Find all orders related to the trader based on their ID (the trader is associated with packages)
            $traderOrders = Order::whereHas('package', function ($query) use ($traderId) {
                $query->where('user_id', $traderId); // Assuming 'user_id' in packages table is the trader's ID
            })->where('order_status_id', 2) // Filter completed orders
                ->get();
            $totalEarnings = $traderOrders->sum('amount_after_commission');
            // Calculate the remaining amount to be paid to the trader
            $remainingAmount = $totalEarnings - $user->paid_to_trader;

            if ($remainingAmount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No remaining amount to be paid.',
                ], 400);
            }

            // Create a payment request
            $payment = Payment::create([
                'user_id' => $user->id,
                'payment_method_id' => $validated['payment_method_id'],
                'total_amount' => $totalEarnings,
                'remaining_amount' => $remainingAmount,
                'status' => 'pending',
            ]);

            // Update the order_status_id to 7
            $traderOrders->each(function ($order) {
                $order->update([
                    'order_status_id' => 7
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Payment request created successfully.',
                'data' => $payment,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function makePayment(Request $request)
    {
        $validated = $request->validate([
            'paid_amount' => 'required|numeric|min:0',
            'paymentId' => 'required|numeric|exists:payments,id', // Ensure paymentId exists in payments table
        ]);

        try {
            // Find the payment request using the validated paymentId
            $payment = Payment::findOrFail($validated['paymentId']);

            // Ensure you're not paying more than the remaining amount
            if ($validated['paid_amount'] > $payment->remaining_amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount exceeds remaining balance.',
                ], 400);
            }

            // Update the payment record
            $payment->paid_amount += $validated['paid_amount'];
            $payment->remaining_amount -= $validated['paid_amount'];

            // Update the status
            if ($payment->remaining_amount == 0) {
                $payment->status = 'paid';
            } else {
                $payment->status = 'partially_paid';
            }

            $payment->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment updated successfully.',
                'data' => $payment,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    


}
