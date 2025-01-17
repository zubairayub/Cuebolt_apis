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
            // Add the market_pair_id column (unsigned integer)
        $table->unsignedBigInteger('market_pair_id')->nullable();

        // Add the foreign key constraint
        $table->foreign('market_pair_id')->references('id')->on('market_pairs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signal_performance', function (Blueprint $table) {
            $table->dropForeign(['market_pair_id']);
            $table->dropColumn('market_pair_id');
        });
    }
};
