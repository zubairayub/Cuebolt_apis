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
        Schema::create('user_reviews', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();  // This is the ID of the user who wrote the review (reviewer)
            $table->bigInteger('trader_id')->unsigned(); // This is the ID of the user being reviewed (trader)
            $table->integer('rating')->default(0); 
            $table->text('review');
            $table->string('reviewer_location')->nullable();  // You can still store location if needed
            $table->timestamps();
    
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); 
            $table->foreign('trader_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_reviews');
    }
};
