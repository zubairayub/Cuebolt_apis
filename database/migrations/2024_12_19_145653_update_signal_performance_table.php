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
        Schema::table('signal_performance', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Link to users table
            $table->decimal('entry_price', 10, 2); // Entry price for the signal
            $table->decimal('take_profit', 10, 2); // Take profit value
            $table->decimal('stop_loss', 10, 2); // Stop loss value
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signal_performance', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'entry_price', 'take_profit', 'stop_loss']);
        });
    }
};
