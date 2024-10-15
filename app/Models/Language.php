<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = ['name'];

    // Many-to-many relationship with UserProfile
    public function userProfiles()
    {
        return $this->belongsToMany(UserProfile::class, 'language_user_profile');
    }
}
