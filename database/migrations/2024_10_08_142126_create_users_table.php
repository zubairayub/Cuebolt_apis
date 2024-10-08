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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique()->nullable();   // Allow login with username
            $table->string('phone')->unique()->nullable();      // Allow login with phone
            $table->string('email')->unique();                  // Login with email
            $table->timestamp('email_verified_at')->nullable(); // For email verification
            $table->string('password');                         // Password for authentication
            $table->rememberToken();                            // Remember me token
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');  // Foreign key for roles
            $table->string('otp')->nullable();                  // OTP for verification
            $table->timestamp('phone_verified_at')->nullable(); // For phone verification
            $table->boolean('is_guest')->default(false);        // For guest access
            $table->string('social_id')->nullable();            // For social login
            $table->timestamps();                               // Timestamps for created_at and updated_at
        });
        

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
