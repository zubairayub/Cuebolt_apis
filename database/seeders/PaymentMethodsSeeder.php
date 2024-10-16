<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentMethod::create(['method_name' => 'Visa']);
        PaymentMethod::create(['method_name' => 'MasterCard']);
        PaymentMethod::create(['method_name' => 'PayPal']);
        PaymentMethod::create(['method_name' => 'Crypto']);
        PaymentMethod::create(['method_name' => 'Apple Pay']);
    }
}
