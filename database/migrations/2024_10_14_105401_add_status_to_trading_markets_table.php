<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToTradingMarketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trading_markets', function (Blueprint $table) {
            // Add the status column with enum values ('active' or 'inactive')
            $table->enum('status', ['active', 'inactive'])->default('active')->after('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trading_markets', function (Blueprint $table) {
            // Drop the status column if we ever need to rollback this migration
            $table->dropColumn('status');
        });
    }
}
