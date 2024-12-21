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
        Schema::table('trades', function (Blueprint $table) {
            $table->decimal('take_profit', 16, 8)->nullable()->change();
            $table->decimal('take_profit_2', 16, 8)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->decimal('take_profit', 10, 3)->nullable()->change();
            $table->decimal('take_profit_2', 10, 3)->nullable()->change();
        });
    }
};
