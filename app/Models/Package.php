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
    ];

    // Attributes to cast to specific data types
    protected $casts = [
        'price' => 'decimal:2',
        'signals_count' => 'integer',
        'risk_reward_ratio' => 'decimal:2',
        'status' => 'boolean', // To handle the tinyint(1) for active/inactive
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

    
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
