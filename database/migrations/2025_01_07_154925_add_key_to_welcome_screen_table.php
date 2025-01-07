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
        Schema::table('welcome_screen', function (Blueprint $table) {
            $table->string('key')->nullable();  // Adding 'key' column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('welcome_screen', function (Blueprint $table) {
            $table->dropColumn('key');  // Dropping 'key' column if migration is rolled back
        });
    }
};
