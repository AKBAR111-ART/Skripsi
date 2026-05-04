<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('app:aggregate10min')->everyTenMinutes();
Schedule::command('app:aggregatedaily')->daily();
Schedule::command('app:aggregatemonthly')->monthly();
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
