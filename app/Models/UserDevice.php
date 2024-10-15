<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\Token as OAuthAccessToken;  // Add this import at the top of your model

class UserDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token_id',
        'device',
        'platform',
        'browser',
        'ip_address'
    ];

    // Relationship to the user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to the OAuth token
    public function token()
    {
        return $this->belongsTo(OAuthAccessToken::class, 'token_id');
    }
}
