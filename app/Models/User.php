<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'phone',
        'password',
        'role_id',
        'otp',
        'email_verified_at',
        'facebook_id',
        'google_id',
        'fcm_token',
        'social_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    public function signals()
    {
        return $this->hasMany(TradingSignal::class);
    }
 
    public function reviews()
    {
        return $this->hasMany(UserReview::class, 'trader_id');
    }
    
    public function faqs()
    {
        return $this->hasMany(UserFaq::class); // Assuming a User has many FAQs
    }

   
    
}
