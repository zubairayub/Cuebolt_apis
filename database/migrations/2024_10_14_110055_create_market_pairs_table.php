<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketPairsTable extends Migration
{
    public function up()
    {
        Schema::create('market_pairs', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('market_id'); // Foreign key referencing trading_markets.id
            $table->string('base_currency'); // Base currency (e.g., BTC)
            $table->string('quote_currency'); // Quote currency (e.g., USDT)
            $table->decimal('price', 18, 8); // Current price
            $table->enum('status', ['active', 'inactive'])->default('active'); // Status of the pair
            $table->string('icon')->nullable(); // Icon link for the pair
            $table->text('description')->nullable(); // Description for the pair
            $table->timestamps();

            // Define foreign key constraint
            $table->foreign('market_id')->references('id')->on('trading_markets')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('market_pairs');
    }
}
