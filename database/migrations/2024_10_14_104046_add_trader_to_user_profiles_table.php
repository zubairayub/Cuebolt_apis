<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTraderToUserProfilesTable extends Migration
{
    public function up()
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            // Add the new 'trader' column with a default value of 0
            $table->tinyInteger('trader')->default(0)->after('user_id');  // Replace 'existing_column' with the name of the column after which you want to place the new column
        });
    }

    public function down()
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            // Rollback: Drop the 'trader' column if rolling back
            $table->dropColumn('trader');
        });
    }
}
