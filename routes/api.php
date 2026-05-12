<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FeedingController;
use App\Http\Controllers\SensorController;

/*
|--------------------------------------------------------------------------
| API Routes (IoT Tambak Udang)
|--------------------------------------------------------------------------
*/

// FEEDING (ESP32 / sistem pakan otomatis)
Route::post('/feeding/{shift}', [FeedingController::class, 'update']);

// SENSOR (ESP32 kirim data pH & turbidity)
Route::post('/sensor', [SensorController::class, 'store']);

// OPTIONAL: ambil data sensor terbaru (dashboard realtime)
Route::get('/sensor/latest', [SensorController::class, 'latest']);