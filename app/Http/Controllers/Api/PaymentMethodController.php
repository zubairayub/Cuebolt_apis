<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\UserPaymentDetail;
use App\Models\Bank;
use App\Models\Wallet;
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
        // Fetch all payment methods from the database
        $paymentMethods = PaymentMethod::all();

        // Group the payment methods by 'method_name' (treating it as a type)
        $groupedMethods = $paymentMethods->groupBy('method_name');

        // Transform the grouped data for API response
        $result = $groupedMethods->map(function ($methods, $name) {
            return [
                'type' => $name,  // Use the method_name as the type
                'methods' => $methods->map(function ($method) {
                    return [
                        'id' => $method->id,
                        'name' => $method->method_name,  // Correct column name
                    ];
                }),
            ];
        })->values();

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

}
