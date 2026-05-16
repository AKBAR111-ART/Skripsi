<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Schedule::command('pakan:kirim')->everyMinute();
Schedule::command('pakan:kirim')
    ->everyMinute();
Schedule::command('wa:send-scheduled')
    ->everyMinute()
    ->withoutOverlapping();


Schedule::command('feeding:daily-create')->dailyAt('00:01');
Schedule::command('feeding:update-total')->everyFiveMinutes();
Schedule::command('app:aggregate5min')->everyFiveMinutes();
Schedule::command('app:aggregatedaily')->dailyAt('23:59');
Schedule::command('app:aggregate5min')->everyFiveMinutes();
Schedule::command('app:aggregatedaily')->daily();
Schedule::command('app:aggregatemonthly')->monthly();
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
