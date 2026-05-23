<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeedingController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\FeedingHistoryController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
/*
|--------------------------------------------------------------------------
| API Routes (IoT Tambak Udang)
|--------------------------------------------------------------------------
*/

// Sensor endpoints
Route::get('/sensor/realtime', [SensorController::class, 'realtime']);
Route::get('/sensor/getFeedingRecommendation', [SensorController::class, 'getFeedingRecommendation']);
Route::post('/sensor/clear-cache', [SensorController::class, 'clearRuleCache']);
// Calibration endpoints
Route::post('/sensor/calibrate-ph', [SensorController::class, 'calibratePh']);
Route::post('/sensor/calibrate-turbidity', [SensorController::class, 'calibrateTurbidity']);
Route::post('/sensor/reset-calibration', [SensorController::class, 'resetCalibration']);
Route::get('/sensor/calibration-status', [SensorController::class, 'getCalibrationStatus']);
// Di routes/api.php, tambahkan:
Route::post('/sensor/send-feed-command', [SensorController::class, 'sendFeedCommand']);
Route::get('/sensor/weekly-feed', [SensorController::class, 'getWeeklyFeedData']);
// Endpoint untuk ESP32 mengambil perintah (GET)
Route::get('/sensor/command', [SensorController::class, 'getCommand']);
// Di routes/api.php
Route::get('/realtime', [HomeController::class, 'getRealtimeData']);
// ========== FEEDING HISTORY ==========
Route::prefix('feeding')->group(function () {
    Route::get('/today', [FeedingHistoryController::class, 'today']);
    Route::get('/weekly', [FeedingHistoryController::class, 'weekly']);
    Route::get('/monthly', [FeedingHistoryController::class, 'monthly']);
Route::get('/all', [FeedingHistoryController::class, 'all']);
    Route::post('/manual', [FeedingHistoryController::class, 'manualFeed']);
});
/*
|--------------------------------------------------------------------------
| API Routes (IoT Tambak Udang)
|--------------------------------------------------------------------------
*/

// ==================== KALIBRASI SENSOR ====================
Route::post('/calibration/reset', [SensorController::class, 'resetCalibration']);
Route::post('/calibrate/ph', [SensorController::class, 'calibratePh']);
Route::post('/calibrate/turbidity', [SensorController::class, 'calibrateTurbidity']);
Route::get('/calibration/status', [SensorController::class, 'getCalibrationStatus']);
// Profile Data API
Route::get('/profile/data', [ProfileController::class, 'getProfileData']);
// ========== SENSOR ==========
Route::post('/sensor/data', [SensorController::class, 'store']);
Route::post('/sensor', [SensorController::class, 'store']);
Route::get('/sensor/latest', [SensorController::class, 'latest']);
Route::get('/sensor/history', [SensorController::class, 'history']);
Route::get('/realtime', [SensorController::class, 'realtime']);
Route::get('/feeding/recommendation', [SensorController::class, 'getFeedingRecommendation']);

// ========== FEEDING ==========
Route::post('/feeding/{shift}', [FeedingController::class, 'update']);

// ========== FEEDING HISTORY (TAMBAHKAN INI) ==========
Route::prefix('feeding')->group(function () {
    Route::get('/today', [FeedingHistoryController::class, 'today']);
    Route::get('/weekly', [FeedingHistoryController::class, 'weekly']);
    Route::get('/monthly', [FeedingHistoryController::class, 'monthly']);
    Route::get('/all', [FeedingHistoryController::class, 'all']);
    Route::post('/manual', [FeedingHistoryController::class, 'manualFeed']);
});

// ========== SEND PAKAN ==========
Route::post('/send-pakan', [FeedingHistoryController::class, 'manualFeed']);
Route::get('/rule/latest', [PengaturanController::class, 'getLatestRule']);