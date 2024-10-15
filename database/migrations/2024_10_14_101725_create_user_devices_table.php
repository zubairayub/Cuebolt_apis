<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDevicesTable extends Migration
{
    public function up()
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('token_id', 100);  // Match the varchar(100) size from oauth_access_tokens
            $table->foreign('token_id')->references('id')->on('oauth_access_tokens')->onDelete('cascade');
            $table->string('device')->nullable();
            $table->string('platform')->nullable();
            $table->string('browser')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
        });
        
    }

    public function down()
    {
        Schema::dropIfExists('user_devices');
    }
}
