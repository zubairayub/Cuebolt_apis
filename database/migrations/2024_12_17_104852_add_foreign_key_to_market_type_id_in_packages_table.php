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
        Schema::table('packages', function (Blueprint $table) {
           // $table->foreignId('market_type_id')->constrained('trading_markets')->onDelete('cascade'); // Link to signals table
        });

      
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
           // $table->dropForeign(['market_type_id']);
        });
    }
};
