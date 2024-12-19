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
        return $this->belongsTo(Trade::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
