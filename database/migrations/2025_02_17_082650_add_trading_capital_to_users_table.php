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
        Schema::table('users', function (Blueprint $table) {
            // Adding trading_capital column to the users table
            $table->decimal('trading_capital', 15, 2)->default(0)->after('email'); // Example: You can place it after 'email'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Dropping the trading_capital column if rolling back
            $table->dropColumn('trading_capital');
        });
    }
};
