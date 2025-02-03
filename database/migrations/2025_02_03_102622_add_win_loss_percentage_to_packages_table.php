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
            $table->decimal('win_percentage', 5, 2)->default(0)->after('profit_loss_percentage'); // Adjust 'existing_column' as per your table
            $table->decimal('loss_percentage', 5, 2)->default(0)->after('win_percentage'); // Adjust 'existing_column' as per your table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('win_percentage');
            $table->dropColumn('loss_percentage');
        });
    }
};
