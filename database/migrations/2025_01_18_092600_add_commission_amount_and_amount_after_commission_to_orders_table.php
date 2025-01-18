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
            $table->decimal('commission_amount', 10, 2)->nullable()->after('payment_method_id');  // Adjust position if needed
            $table->decimal('amount_after_commission', 10, 2)->nullable()->after('commission_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['commission_amount', 'amount_after_commission']);
        });
    }
};
