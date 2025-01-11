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
        Schema::table('trade_journals', function (Blueprint $table) {
             $table->unsignedBigInteger('emotion_id')->nullable(); // Foreign key linking to the emotions table

            // Add the foreign key constraint
            $table->foreign('emotion_id')->references('id')->on('trade_emotions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_journals', function (Blueprint $table) {
            $table->dropForeign(['emotion_id']);
            $table->dropColumn('emotion_id');
        });
    }
};
