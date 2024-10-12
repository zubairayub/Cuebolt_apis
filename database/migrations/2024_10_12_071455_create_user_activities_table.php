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
    Schema::create('user_activities', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable(); // Nullable for guest users
        $table->string('token'); // Store the auth token here
        $table->string('action'); // Action like login, visit_screen, click_button
        $table->string('screen')->nullable(); // Screen name (optional)
        $table->string('button')->nullable(); // Button clicked (optional)
        $table->timestamp('started_at'); // When the activity started
        $table->timestamp('ended_at')->nullable(); // When the activity ended
        $table->timestamps(); // Laravel's created_at and updated_at columns
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
