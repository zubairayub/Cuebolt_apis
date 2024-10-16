<?php

// database/migrations/xxxx_xx_xx_create_orders_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');  // User who made the purchase
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');  // Package being purchased
            $table->decimal('amount', 10, 2);  // The amount paid for the package
            $table->date('expiry_date');  // Expiry date calculated based on the duration
            $table->boolean('auto_renew')->default(false);  // Auto-renewal option
            $table->foreignId('payment_method_id')->constrained('payment_methods');  // Payment method used
            $table->foreignId('order_status_id')->constrained('order_statuses');  // Status of the order
            $table->timestamps();  // created_at and updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}

