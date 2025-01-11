<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TradeImage extends Model
{
    use HasFactory;

    protected $fillable = ['trade_id', 'image_path', 'image_name'];
    
    public function trade()
    {
        return $this->belongsTo(Trade::class, 'trade_id');
    }
    
}
