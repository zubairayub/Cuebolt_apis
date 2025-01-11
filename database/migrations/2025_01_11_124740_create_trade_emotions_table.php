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
        Schema::create('trade_emotions', function (Blueprint $table) {
            $table->id();
            $table->string('emotion_name'); // Name of the emotion (e.g., "Happy", "Anxious")
            $table->timestamps();
        });

        DB::table('trade_emotions')->insert([
            ['emotion_name' => 'Happy'],
            ['emotion_name' => 'Anxious'],
            ['emotion_name' => 'Fearful'],
            ['emotion_name' => 'Confident'],
            ['emotion_name' => 'Uncertain'],
            ['emotion_name' => 'Angry'],
            ['emotion_name' => 'Sad'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_emotions');
    }
};
