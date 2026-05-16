<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;



return Application::configure(basePath: dirname(__DIR__))
->withSchedule(function (Schedule $schedule) {

    $schedule->command('sensor:check')->everyFiveMinutes();
    $schedule->command('pengingat:kirim')->everyMinute();
    $schedule->command('pakan:kirim')->everyMinute();

})
->withSchedule(function (Schedule $schedule) {

    // kirim WA tiap menit
    $schedule->command('pengingat:kirim')->everyMinute();

    // RESET tiap hari jam 00:01
    $schedule->command('pengingat:reset')->dailyAt('00:01');

})
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withSchedule(function (Schedule $schedule) {

        // ⏱️ existing job kamu
        $schedule->command('app:aggregate5min')->everyFiveMinutes();

        // 🔔 alarm makan udang
        $schedule->command('pengingat:kirim')->everyMinute();

        // ⚙️ rule engine sensor PH & turbidity
        $schedule->command('pakan:kirim')->everyMinute();
    })

    ->withMiddleware(function (Middleware $middleware): void {
        //
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })

    ->create();