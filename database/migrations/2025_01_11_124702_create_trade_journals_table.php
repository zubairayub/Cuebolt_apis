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
        Schema::create('trade_journals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trade_id'); // Foreign key linking to the trades table
            $table->text('trade_decision')->nullable(); // Reason for entering the trade
            $table->text('trade_analysis')->nullable(); // Analysis of the trade
            $table->text('trade_reflection')->nullable(); // Reflection after the trade
            $table->text('trade_improvement')->nullable(); // Ideas for improvement
            $table->json('trade_strategy')->nullable(); // The strategy used in the trade
            $table->json('trade_risk_management')->nullable(); // Risk management used in the trade
            $table->timestamps();

            // Add foreign key constraint
            $table->foreign('trade_id')->references('id')->on('trades')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_journals');
    }
};
