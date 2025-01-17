<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignalPerformance extends Model
{
    protected $table = 'signal_performance'; // Explicitly set the table name
    use HasFactory;
    protected $fillable = [
        'signal_id',
        'user_id',
        'current_price',
        'profit_loss',
        'entry_price',
        'take_profit',
        'stop_loss',
        'status',
    ];

    public function trade()
    {
        return $this->belongsTo(Trade::class, 'signal_id', 'id'); // signal_id links to trades.id
    }

    /**
     * Define the relationship to the User model (Many-to-One).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id'); // user_id links to users.id
    }

    public function marketPair()
    {
        return $this->belongsTo(MarketPair::class, 'market_pair_id', 'id'); // market_pair_id is the foreign key
    }


}
