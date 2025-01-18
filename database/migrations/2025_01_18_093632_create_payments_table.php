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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Links to the users table
            $table->foreignId('payment_method_id')->constrained()->onDelete('cascade'); // Bank, Wallet, Crypto
            $table->decimal('total_amount', 10, 2); // Total amount for the payment
            $table->decimal('paid_amount', 10, 2)->default(0); // Amount already paid
            $table->decimal('remaining_amount', 10, 2); // Remaining amount to be paid
            $table->enum('status', ['pending', 'paid', 'partially_paid'])->default('pending'); // Payment status
            $table->timestamps(); // Created and updated timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
