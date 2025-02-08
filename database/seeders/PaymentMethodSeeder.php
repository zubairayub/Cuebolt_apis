<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentMethod::create(['method_name' => 'Debit-Credit Card']);
        PaymentMethod::create(['method_name' => 'Wallet']);
        PaymentMethod::create(['method_name' => 'Crypto']);
        PaymentMethod::create(['method_name' => 'Bank']);
        PaymentMethod::create(['method_name' => 'Paypal']);
    }
}
