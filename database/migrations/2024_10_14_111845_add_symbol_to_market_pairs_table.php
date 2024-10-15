<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSymbolToMarketPairsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('market_pairs', function (Blueprint $table) {
            // Add the 'symbol' column to store the trading pair symbol
            $table->string('symbol', 255)->after('quote_currency')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('market_pairs', function (Blueprint $table) {
            $table->dropColumn('symbol');
        });
    }
}
