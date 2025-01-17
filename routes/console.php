<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\MonitorMarketPairsWebSocket;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();




Artisan::command('monitor:market-pairs-websocket', function () {
    // Instantiate and run the command logic
    app(MonitorMarketPairsWebSocket::class)->handle();
});

