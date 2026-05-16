<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UtamaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TambakProfileController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\PengingatController;
     

Route::get('/sensor/realtime', [SensorController::class, 'realtime']);
Route::post(
    '/pengaturan/rule',
    [PengaturanController::class, 'updateRule']
);
Route::post('/pengaturan/update-rule', [PengaturanController::class, 'updateRule']);

Route::post('/pengingat/store', [PengingatController::class, 'store']);
Route::post('/test-wa', [PengaturanController::class, 'testWa']);
Route::get('/pengaturan', [PengaturanController::class, 'index']);
Route::post('/pengaturan/store', [PengaturanController::class, 'store']);


Route::put(
    '/profile-tambak/biomassa/update',
    [ProfileController::class, 'updateBiomassa']
);
Route::put('/profile-tambak/update', [ProfileController::class, 'update']);
Route::post('/budidaya/reset', [ProfileController::class, 'resetBudidaya']);
Route::put('/budidaya/start', [ProfileController::class, 'startBudidaya']);
Route::put('/profile-tambak/biomassa/update', [ProfileController::class, 'updateBiomassa']);
Route::get('/sensor/latest', [SensorController::class, 'latest']);

// ESP32 kirim data
Route::post('/sensor', [SensorController::class, 'store']);

// Dashboard ambil data realtime
Route::get('/sensor/latest', [SensorController::class, 'latest']);


Route::get('/pengaturan', [PengaturanController::class, 'index']);
Route::post('/pengaturan/store', [PengaturanController::class, 'store']);
Route::post('/pengaturan/save', [PengaturanController::class, 'save']);
Route::get('/pengaturan/history', [PengaturanController::class, 'history']);


// API untuk JS
Route::get('/feeding/history', [PengaturanController::class, 'history']);
Route::put('/profile-tambak/update', [ProfileController::class, 'update']);
Route::put('/biomassa/update', [ProfileController::class, 'updateBiomassa']);
Route::put('/profile-tambak/update', [ProfileController::class, 'update'])->name('profile.update');
Route::middleware(['auth'])->group(function () {
Route::get('/profile', [ProfileController::class, 'index5']);});
Route::put('/biomassa/update', [ProfileController::class, 'updateBiomassa']);
Route::get('/akbar', function () {
    return view('welcome');
});


Route::get('', [UtamaController::class, 'index']);
Route::get('/', function () {
    return view('dashboard.home');
});
Route::get('/history', [HistoryController::class, 'index2']);
Route::get('/monitoring', [MonitoringController::class, 'index3']);

Route::get('/profile', [ProfileController::class, 'index5']);
use Illuminate\Support\Facades\Http;

Route::get('/test-wa', function () {

    $response = Http::withHeaders([
        'Authorization' => env('FONNTE_TOKEN')
    ])->post('https://api.fonnte.com/send', [

        'target' => '62895379348181',

        'message' => 'WA Gateway berhasil 🚀'

    ]);

    return $response->body();
});