<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('trade_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');  // Daily, Swing, Scalping, etc.
            $table->text('description')->nullable();  // Optional description
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_types');
    }
};
