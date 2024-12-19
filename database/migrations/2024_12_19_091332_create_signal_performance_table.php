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
        Schema::create('signal_performance', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('signal_id')->constrained('trades')->onDelete('cascade'); // Link to signals table
            $table->decimal('current_price', 10, 2); // Latest crypto price
            $table->decimal('profit_loss', 10, 2)->nullable(); // Profit/Loss calculation
            $table->string('status')->default('active'); // 'active', 'hit_take_profit', 'hit_stop_loss'
            $table->timestamps(); // Created and updated timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signal_performance');
    }
};
