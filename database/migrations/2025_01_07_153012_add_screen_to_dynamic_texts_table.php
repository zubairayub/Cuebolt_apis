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
        Schema::create('dynamic_texts', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();  // Unique key to identify the dynamic text
            $table->text('text');  // The text content with HTML formatting
            $table->string('screen'); // Add the 'screen' column
            $table->timestamps();  // Timestamps for created_at and updated_at
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_texts');
    }
};
