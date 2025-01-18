<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserNotification extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'notification_id', 'seen', 'seen_at'];

    public function notification()
    {
        return $this->belongsTo(Notifications::class);
    }
}
