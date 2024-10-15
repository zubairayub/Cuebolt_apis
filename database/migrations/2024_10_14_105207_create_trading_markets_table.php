<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradingMarketsTable extends Migration
{
    public function up()
    {
        Schema::create('trading_markets', function (Blueprint $table) {
            $table->id();  // Primary key for the table
            $table->string('name');  // Name of the trading market (e.g., "Crypto", "Stocks")
            $table->text('description')->nullable();  // Optional description of the market
            $table->timestamps();  // Created at and updated at timestamps
        });
    }

    public function down()
    {
        Schema::dropIfExists('trading_markets');
    }
}
