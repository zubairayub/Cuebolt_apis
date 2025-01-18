<?php

// app/Models/PaymentMethod.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    // Define fillable fields
    protected $fillable = [
        'method_name',
    ];

    // Relationship: A payment method can be used in many orders
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    // In App\Models\PaymentMethod
    public function userPaymentDetails()
    {
        return $this->hasMany(UserPaymentDetail::class, 'payment_method_id', 'id');
    }

    

}

