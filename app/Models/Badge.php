<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;
    protected $fillable = ['description', 'icon', 'status'];
    protected $appends = ['icon_url'];
    
    public function userProfiles()
    {
        return $this->hasMany(UserProfile::class, 'badge_id');
    }

    public function getIconUrlAttribute()
    {
        return $this->icon 
            ? asset('storage/app/public/uploads/images/badges/' . $this->icon)  
            : asset('storage/app/public/uploads/images/badges/default_badge_icon.png');
    }
}
