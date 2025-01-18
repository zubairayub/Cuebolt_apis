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
        Schema::create('commissions', function (Blueprint $table) {
            $table->id(); // This will create an unsigned big integer by default
            $table->string('name');  // Name of the commission (e.g., "default commission", "premium trader commission")
            $table->decimal('percentage', 5, 2);  // Commission percentage (e.g., 10.50)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
