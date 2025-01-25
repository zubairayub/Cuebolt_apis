<?php

// app/Models/Order.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'amount',
        'expiry_date',
        'auto_renew',
        'payment_method_id',
        'order_status_id',
        'commission_id',
        'commission_amount',
        'amount_after_commission',
        'stripe_subscription_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function orderStatus()
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function commission()
    {
        return $this->belongsTo(Commission::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'user_id');  // Assuming 'user_id' in orders table relates to User
    }
}

