<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;

class PaymentMethodController extends Controller
{
    // Fetch all payment methods
    public function index()
    {
        $paymentMethods = PaymentMethod::all();
        return response()->json($paymentMethods, 200);
    }

    // Fetch a specific payment method by ID
    public function show($id)
    {
        $paymentMethod = PaymentMethod::find($id);

        if (!$paymentMethod) {
            return response()->json(['message' => 'Payment method not found'], 404);
        }

        return response()->json($paymentMethod, 200);
    }
}
