<?php

// app/Models/OrderStatus.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    use HasFactory;

    // Define fillable fields
    protected $fillable = [
        'status_name',
    ];

    // Relationship: An order status can be linked to many orders
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
