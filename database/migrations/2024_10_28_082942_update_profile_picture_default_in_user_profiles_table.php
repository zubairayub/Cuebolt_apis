<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProfilePictureDefaultInUserProfilesTable extends Migration
{
    public function up()
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            // Set a default value for profile_picture if it's null
            $table->string('profile_picture')->nullable()->default('images/default_profile_picture.png')->change();
        });
    }

    public function down()
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('profile_picture')->nullable()->default(null)->change();
        });
    }
}
