<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    use HasFactory;

    // Define the table name (optional if it matches the model name)
    protected $table = 'trades';

    // Define fillable attributes
    protected $fillable = [
        'package_id',
        'market_pair_id',
        'trade_type_id',
        'trade_name',
        'trade_date',
        'entry_price',
        'take_profit',
        'take_profit_2',
        'stop_loss',
        'profit_loss',
        'time_frame',
        'validity',
        'status',
        'notes',  
    ];

    /**
     * Define the relationship to the Package model (Many-to-One).
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Define the relationship to the MarketPair model (Many-to-One).
     */
    public function marketPair()
    {
        return $this->belongsTo(MarketPair::class, 'market_pair_id');
    }

    /**
     * Define the relationship to the TradeType model (Many-to-One).
     */
    public function tradeType()
    {
        return $this->belongsTo(TradeType::class, 'trade_type_id');
    }

    /**
     * Define the relationship to the SignalPerformance model (One-to-One).
     */
    public function signalPerformance()
    {
        return $this->hasMany(SignalPerformance::class, 'signal_id', 'id'); // trades.id links to signal_performance.signal_id
    }

    public function isFollowedByUser($userId)
    {
        return $this->signalPerformance()->where('user_id', $userId)->exists();
    }

    public function images()
    {
        return $this->hasMany(TradeImage::class, 'trade_id');
    }

    public function tradeJournal()
    {
        return $this->hasOne(TradeJournal::class, 'trade_id'); // Assuming the foreign key in TradeJournal is 'trade_id'
    }
    
}
