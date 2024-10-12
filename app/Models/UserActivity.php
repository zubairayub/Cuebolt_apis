<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','token', 'action', 'screen', 'button', 'started_at', 'ended_at'];
}
