<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    // If your table name doesn't follow the default Laravel convention, define it explicitly
    protected $table = 'user_profiles';  // Change if your table name is different

    // Define fillable attributes to protect against mass assignment
    protected $fillable = [
        'user_id', 
        'trader',
        'rating', 
        'short_info', 
        'total_signals',
        'total_packages', 
        'win_percentage', 
        'rrr', 
        'status', 
        'users_count',
        'about',
        'deals_in', 
        'contact_info', 
        'member_since', 
        'average_response_time', 
        'location',
        'country_id',
        'city_id', 
    ];

    // Define relationships if necessary, for example, to the user table
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    // Many-to-many relationship with Language
    public function languages()
    {
        return $this->belongsToMany(Language::class, 'language_user_profile');
    }
    // Relationship with country
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    // Relationship with city
    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
