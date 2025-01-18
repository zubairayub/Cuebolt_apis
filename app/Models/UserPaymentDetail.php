<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPaymentDetail extends Model
{
    protected $fillable = ['user_id', 'payment_method_id', 'details'];

    protected $casts = [
        'details' => 'array', // Automatically handle JSON serialization/deserialization
    ];
}
