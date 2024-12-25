<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('stripe_subscription_id')->nullable()->after('package_id'); // Add Stripe subscription ID
            $table->timestamp('expires_at')->nullable()->after('stripe_subscription_id'); // Add expiry date
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['stripe_subscription_id', 'expires_at']); // Drop the added columns
        });
    }
};
