<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\MonitorMarketPairsWebSocket;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();




Artisan::command('monitor:market-pairs-websocket', function () {
    // Instantiate and run the command logic
    app(MonitorMarketPairsWebSocket::class)->handle();
});


//below if for cronjob
// * * * * * php /path/to/your/project/artisan schedule:run >> /dev/null 2>&1

// Schedule::command('monitor:market-pairs-websocket')
//     ->hourly()
//     ->withoutOverlapping();

