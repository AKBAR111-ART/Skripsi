<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorController;

Route::post('/sensor', [SensorController::class, 'store']);
Route::get('/sensor/latest', function () {
    return response()->json([
        'ph' => 7.2,
        'turbidity' => 30
    ]);
});