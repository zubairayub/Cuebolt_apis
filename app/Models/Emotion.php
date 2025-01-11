<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Emotion extends Model
{
    use HasFactory;

    // The table associated with the model.
    protected $table = 'trade_emotions';

    // The attributes that are mass assignable.
    protected $fillable = ['emotion_name'];

    /**
     * Get all the trade journals related to this emotion.
     */
    public function tradeJournals()
    {
        return $this->hasMany(TradeJournal::class, 'emotion_id');
    }
}
