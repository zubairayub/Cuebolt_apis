<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();  // Auto-incrementing ID
            $table->unsignedBigInteger('package_id');      // Reference to packages table
            $table->unsignedBigInteger('market_pair_id');  // Reference to market_pairs table
            $table->unsignedBigInteger('trade_type_id');   // Reference to trade_types table
            $table->string('trade_name');                  // Name of the trade
            $table->date('trade_date');                    // Trade date
            $table->decimal('entry_price', 15, 8);         // Entry price
            $table->decimal('take_profit', 15, 8);         // Take profit value
            $table->decimal('stop_loss', 15, 8);           // Stop loss value
            $table->decimal('profit_loss', 15, 8)->nullable(); // Profit or loss
            $table->string('time_frame');                  // Trade time frame
            $table->string('validity');                    // Trade validity (e.g., "active", "expired")
            $table->tinyInteger('status')->default(1);     // Status (1 = active, 0 = inactive)
            $table->timestamps();                          // Created and updated timestamps

            // Define foreign key constraints
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
            $table->foreign('market_pair_id')->references('id')->on('market_pairs')->onDelete('cascade');
            $table->foreign('trade_type_id')->references('id')->on('trade_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trades');
    }
}
