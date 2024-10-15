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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->string('short_info')->nullable(); 
            $table->decimal('rating', 3, 2)->default(0); 
            $table->integer('total_signals')->default(0); 
            $table->integer('total_packages')->default(0); 
            $table->decimal('win_percentage', 5, 2)->default(0); 
            $table->string('rrr')->nullable(); 
            $table->string('status')->default('offline'); 
            $table->integer('users_count')->default(0); 
            $table->string('about')->nullable(); 
            $table->string('deals_in')->nullable(); 
            $table->string('contact_info')->nullable(); 
            $table->string('member_since')->nullable(); 
            $table->string('average_response_time')->nullable(); 
            $table->json('languages')->nullable(); 
            $table->string('location')->nullable(); 
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
