<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use App\Models\Order;
use App\Models\Package;
use Stripe\Customer;
use Stripe\Subscription;

class SubscriptionController extends Controller
{
        public function createSubscription(Request $request)
        {
            
            $validated = $request->validate([
                'package_id' => 'required|integer',
            ]);
           

            $user = auth()->user();
            $package = Package::findOrFail($validated['package_id']);

            Stripe::setApiKey(env('STRIPE_SECRET'));

            // Create a Stripe Customer if not exists
            if (!$user->stripe_customer_id) {
                $customer = Customer::create([
                    'email' => $user->email,
                    'metadata' => ['user_id' => $user->id],
                ]);

                $user->update(['stripe_customer_id' => $customer->id]);
            }

            // Create a Stripe Subscription
            $subscription = Subscription::create([
                'customer' => $user->stripe_customer_id,
                'items' => [[
                    'price' => $package->stripe_price_id,
                ]],
            ]);

            // Save subscription details to the database
            Order::create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'stripe_subscription_id' => $subscription->id,
                'status' => 'active',
                'expires_at' => now()->addDays($package->billing_interval === 'monthly' ? 30 : 365),
            ]);

            return response()->json(['subscription' => $subscription]);
        }
}
