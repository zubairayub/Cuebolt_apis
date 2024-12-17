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
            $table->decimal('achieved_rrr', 5, 2)->nullable()->default(null); // Nullable decimal for achieved RRR
            $table->decimal('from_amount', 10, 2)->nullable()->default(null); // Nullable decimal for from_amount
            $table->decimal('to_amount', 10, 2)->nullable()->default(null); // Nullable decimal for to_amount
            $table->integer('challenge_days')->nullable()->default(null); // Nullable integer for challenge_days
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'achieved_rrr',
                'from_amount',
                'to_amount',
                'challenge_days',
            ]);
        });
    }
};
