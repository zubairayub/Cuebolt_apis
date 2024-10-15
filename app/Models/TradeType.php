<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeType extends Model
{
    use HasFactory;

    // Define the table name (optional if it matches the model name)
    protected $table = 'trade_types';

    // Define fillable attributes
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Define the relationship to the Trade model (One-to-Many).
     */
    public function trades()
    {
        return $this->hasMany(Trade::class);
    }
}
