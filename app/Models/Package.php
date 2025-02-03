<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; // Add this line for the Storage facade

class Package extends Model
{
    use HasFactory;

    // Define the table name if it's different from the default (which Laravel assumes is 'packages')
    protected $table = 'packages';

    // Define the primary key column
    protected $primaryKey = 'id';

    // Disable timestamps if not present
    public $timestamps = true;
    protected $appends = ['picture_url'];

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
        'stripe_product_id',
        'stripe_price_id',
        'profit_loss_percentage',
        'win_percentage',
        'loss_percentage',

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
          // Generate the full URL for the picture, or fallback to the default image
          return $this->picture 
              ? asset('storage/app/public/' . $this->picture) // Prepend 'storage/app/public'
              : asset('storage/app/public/uploads/images/packages/default_package_picture.png');
      }
  
    // Set default behavior during model boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($package) {
            // Assign a default picture if none is provided during creation
            if (empty($package->picture)) {
                $package->picture = 'uploads/images/packages/default_package_picture.png'; // Path to your default image
            }
        });

        static::updating(function ($package) {
            // Ensure that updates retain the current picture or set a default if empty
            if (empty($package->picture)) {
                $package->picture = 'uploads/images/packages/default_package_picture.png'; // Path to your default image
            }
        });
    }

    public function trader()
    {
        return $this->belongsTo(User::class, 'user_id'); // Assuming 'user_id' in package table refers to the trader
    }
}
