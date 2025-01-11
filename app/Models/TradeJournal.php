<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TradeJournal extends Model
{
    use HasFactory;

    // The table associated with the model.
    protected $table = 'trade_journals';

    // The attributes that are mass assignable.
    protected $fillable = [
        'trade_id',
        'trade_decision',
        'trade_analysis',
        'trade_reflection',
        'trade_improvement',
        'trade_strategy',
        'trade_risk_management',
        'emotion_id'
    ];

    /**
     * Get the emotion associated with this trade journal.
     */
    public function emotion()
    {
        return $this->belongsTo(Emotion::class, 'emotion_id');
    }
    public function trade()
    {
        return $this->belongsTo(Trade::class, 'trade_id'); // Assuming the foreign key in TradeJournal is 'trade_id'
    }
}
