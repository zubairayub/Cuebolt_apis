<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketPair extends Model
{
    use HasFactory;

    // Define the table name (optional if it matches the model name)
    protected $table = 'market_pairs';

    // Define fillable attributes
    protected $fillable = [
        'market_id',
        'base_currency',
        'quote_currency',
        'symbol',
        'price',
        'status',
        'icon',
        'description',
    ];

    // Define relationships if needed

    /**
     * Define the relationship to the Trade model (One-to-Many).
     */
    public function trades()
    {
        return $this->hasMany(Trade::class, 'market_pair_id');
    }
}
