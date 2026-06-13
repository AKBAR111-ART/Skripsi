<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\ApiKeyMiddleware::class);
})
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\ApiKeyMiddleware::class);
})
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'auth.custom' => \App\Http\Middleware\AuthMiddleware::class,
    ]);
})
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        
        // PENGINGAT JADWAL WA (SETIAP MENIT)
        // $schedule->command('pengingat:jadwal')->everyMinute();
        
        // FEEDING & PAKAN
        $schedule->command('feeding:schedule')->everyMinute();
        $schedule->command('pakan:kirim')->everyMinute();
        $schedule->command('feeding:daily-create')->dailyAt('00:01');
        $schedule->command('feeding:update-total')->everyFiveMinutes();
        
        // SENSOR
        $schedule->command('sensor:flush-buffer')->everyFiveMinutes();
        
        // AGGREGATE
        $schedule->command('app:aggregate5min')->everyFiveMinutes();
        $schedule->command('app:aggregateDaily')->dailyAt('00:00');
        $schedule->command('app:aggregateMonthly')->monthly();
        
        // PENGINGAT LAIN
        $schedule->command('pengingat:kirim')->everyMinute();
        $schedule->command('pengingat:reset')->dailyAt('00:01');
        
    })
    ->create();