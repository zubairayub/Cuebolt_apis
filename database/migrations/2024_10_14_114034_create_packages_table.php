<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();  // Foreign key for trader (who creates the package)
            $table->string('name');  // Package name
            $table->text('description')->nullable();  // Description of the package
            $table->enum('package_type', ['daily', 'weekly', 'monthly', 'yearly', 'bi_yearly']);  // Type of package
            $table->integer('signals_count')->nullable();  // Number of signals (NULL for unlimited)
            $table->decimal('risk_reward_ratio', 5, 2);  // Risk/reward ratio (e.g., 2.5)
            $table->decimal('price', 10, 2);  // Price of the package
            $table->foreignId('duration_id')->constrained('durations');  // Foreign key to duration table (daily, weekly, etc.)
            $table->string('picture')->nullable();  // URL or path to the image (optional)
            $table->boolean('status')->default(true);  // Active or inactive status
            $table->timestamps();  // created_at and updated_at
        });

        // Adding foreign key constraints
        Schema::table('packages', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('packages');
    }
}
