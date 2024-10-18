<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradingMarket extends Model
{
    use HasFactory;
    
    // Define the table name (optional if it matches the model name)
    protected $table = 'trading_markets';

    // Define fillable attributes
    protected $fillable = [
        'name',
        'description',
        'status',
    ];
}
