<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OrderStatus;

class OrderStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OrderStatus::create(['status_name' => 'Pending']);
        OrderStatus::create(['status_name' => 'Completed']);
        OrderStatus::create(['status_name' => 'Failed']);
        OrderStatus::create(['status_name' => 'Cancelled']);
        OrderStatus::create(['status_name' => 'Refund']);
        OrderStatus::create(['status_name' => 'Trader-Paid']);
        OrderStatus::create(['status_name' => 'Trader-Requested']);
    }
}
