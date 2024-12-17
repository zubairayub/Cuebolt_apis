<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    // Define the table name if it's different from the default (which Laravel assumes is 'packages')
    protected $table = 'packages';

    // Define the primary key column
    protected $primaryKey = 'id';

    // Disable timestamps if not present
    public $timestamps = true;

    // Fields that are mass assignable
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'package_type',
        'signals_count',
        'risk_reward_ratio',
        'price',
        'duration_id',
        'picture',
        'status',
        'is_challenge',          // New field
        'market_type_id',        // Foreign Key to trading_markets
        'achieved_rrr',          // Nullable field
        'from_amount',           // Nullable field
        'to_amount',             // Nullable field
        'challenge_days',        // Nullable field
    ];

    // Attributes to cast to specific data types
    protected $casts = [
        'price' => 'decimal:2',
        'signals_count' => 'integer',
        'risk_reward_ratio' => 'decimal:2',
        'status' => 'boolean', // To handle the tinyint(1) for active/inactive
        'is_challenge' => 'boolean', // Cast to boolean for challenge status
        'achieved_rrr' => 'decimal:2', // For RRR
        'from_amount' => 'decimal:2',  // For amount fields
        'to_amount' => 'decimal:2',
        'challenge_days' => 'integer', // For challenge days
    ];

    // Relationship with the User model (trader)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with the Duration model
    public function duration()
    {
        return $this->belongsTo(Duration::class);
    }

    public function marketType()
    {
        return $this->belongsTo(TradingMarket::class, 'market_type_id');
    }

    
    public function orders()
    {
        return $this->hasMany(Order::class);
    }


    public function trades()
    {
        return $this->hasMany(Trade::class); // Assuming a package has many trades
    }

    public function userProfile()
    {
        return $this->belongsTo(UserProfile::class);
    }

      // Default picture accessor
      public function getPictureUrlAttribute()
      {
          return $this->picture 
              ? asset('storage/' . $this->picture) 
              : asset('public/images/packages/default_package_picture.png');
      }
  
      // You can also set the default image during package creation (when picture is not provided)
      protected static function boot()
      {
          parent::boot();
  
          static::creating(function ($package) {
              if (!$package->picture) {
                  $package->picture = 'images/packages/default_package_picture.png'; // Path to your default image
              }
          });
      }
}
