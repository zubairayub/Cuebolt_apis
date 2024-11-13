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
        'stop_loss',
        'profit_loss',
        'time_frame',
        'validity',
        'status',
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

    
}
