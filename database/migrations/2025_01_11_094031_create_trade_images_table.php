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
        Schema::create('trade_images', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->unsignedBigInteger('trade_id'); // Foreign Key
            $table->string('image_path'); // Image file path
            $table->string('image_name')->nullable(); // Optional image name
            $table->timestamps();

            // Foreign Key Constraint
            $table->foreign('trade_id')->references('id')->on('trades')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_images');
    }
};
