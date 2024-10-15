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
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('country_id')->nullable(); // Nullable in case not provided
            $table->unsignedBigInteger('city_id')->nullable(); // Nullable in case not provided

            // Adding foreign key constraints
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            
            $table->dropForeign(['country_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn(['country_id', 'city_id']);
            
        });
    }
};
