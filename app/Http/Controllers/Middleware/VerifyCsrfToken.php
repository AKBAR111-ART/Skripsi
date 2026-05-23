<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/*',           // Semua route yang dimulai dengan api/
        'api/sensor/data',
        'api/sensor/latest',
        'api/sensor/history',
        'api/realtime',
        'api/send-pakan',
        'api/save-pengaturan',
        'api/save-rule',
    ];
}
