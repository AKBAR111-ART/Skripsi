<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\FeedingHistoryController;
use App\Http\Controllers\PengingatJadwalController;

/*
|--------------------------------------------------------------------------
| WEB ROUTES (TAMBAK UDANG)
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\DashboardController;


// ========== AUTHENTICATION ROUTES ==========
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/logout', [LoginController::class, 'logout']);

Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');

// ========== DASHBOARD (Protected) ==========
Route::middleware('auth.custom')->group(function () {
    Route::get('/dashboard/home', [DashboardController::class, 'home'])->name('home');
});

// ... route Anda yang lain tetap di sini ...
// ========================================================================
//                         AUTHENTICATION ROUTES
// ========================================================================

// Guest routes (belum login) - Login System
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{id}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');
});

// Authenticated routes (wajib login) - Custom Auth Middleware
Route::middleware('auth.custom')->group(function () {
    // Dashboard Home (Login System)
    Route::get('/dashboard/home', [DashboardController::class, 'home'])->name('home');
    
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/logout', [LoginController::class, 'logout']);
});

// ========================================================================
//                         REDIRECT ROOT
// ========================================================================

Route::get('/', function () {
    if (session()->has('user_id')) {
        return redirect('/dashboard/home');
    }
    return redirect('/login');
});

// ========================================================================
//                         PROFILE ROUTES
// ========================================================================

Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

// ========================================================================
//                         API ROUTES (SENSOR & KALIBRASI)
// ========================================================================

Route::get('/api/realtime', [ProfileController::class, 'getRealtimeData']);
Route::post('/api/calibrate/ph', [ProfileController::class, 'calibratePh']);
Route::post('/api/calibrate/turbidity', [ProfileController::class, 'calibrateTurbidity']);
Route::post('/api/calibration/reset', [ProfileController::class, 'resetCalibration']);
Route::get('/api/calibration/status', [ProfileController::class, 'getCalibrationStatus']);
Route::get('/sensor/history', [SensorController::class, 'history']);

// ========================================================================
//                         REKOMENDASI PAKAN & CUACA
// ========================================================================

Route::get('/api/feeding/recommendation', [PengaturanController::class, 'getFeedingRecommendation']);
Route::get('/test-weather', function () {
    $apiKey = env('OPENWEATHER_API_KEY');
    
    if (!$apiKey) {
        return response()->json([
            'success' => false,
            'message' => 'OPENWEATHER_API_KEY belum diset di .env'
        ]);
    }
    
    $city = 'Jakarta';
    
    try {
        $response = Http::get("https://api.openweathermap.org/data/2.5/weather", [
            'q' => $city,
            'appid' => $apiKey,
            'units' => 'metric'
        ]);
        
        if ($response->successful()) {
            $data = $response->json();
            
            $weatherMain = $data['weather'][0]['main'] ?? 'Clear';
            $cuaca = match($weatherMain) {
                'Clear' => 'Cerah',
                'Clouds' => 'Berawan',
                'Rain', 'Drizzle' => 'Hujan',
                default => $weatherMain
            };
            
            return response()->json([
                'success' => true,
                'data' => [
                    'cuaca' => $cuaca,
                    'suhu' => $data['main']['temp'],
                    'kelembaban' => $data['main']['humidity'],
                    'intensitas_hujan' => $data['rain']['1h'] ?? 0,
                    'keterangan' => $data['weather'][0]['description']
                ]
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal mengambil data cuaca',
            'error' => $response->body()
        ], $response->status());
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
});

Route::post('/sensor/clear-cache', [SensorController::class, 'clearRuleCache']);

// ========================================================================
//                         PENGATURAN ROUTES
// ========================================================================

Route::get('/api/pengaturan/realtime-sensor', [PengaturanController::class, 'getRealtimeSensor']);
Route::get('/api/jadwal-list', [PengaturanController::class, 'getJadwalList']);
Route::post('/api/jadwal-store', [PengaturanController::class, 'storeJadwal']);
Route::delete('/api/jadwal-delete/{id}', [PengaturanController::class, 'deleteJadwal']);
Route::get('/api/profile/latest', [ProfileController::class, 'getLatestProfile']);

// ========================================================================
//                         HISTORY ROUTES
// ========================================================================

Route::get('/history/week-data/{week}', [HistoryController::class, 'getWeekData']);
Route::get('/history/day-detail/{date}', [HistoryController::class, 'getDayDetail']);
Route::get('/daily-monitoring-data', [HistoryController::class, 'getDailyMonitoring'])->name('daily-monitoring-data');
Route::get('/api/daily-monitoring', [HistoryController::class, 'getDailyMonitoring'])->name('api.daily-monitoring');
Route::get('/monitoring/data', [MonitoringController::class, 'getMonitoringData'])->name('monitoring.data');

// ========================================================================
//                         MONITORING ROUTES
// ========================================================================

Route::get('/monitoring/realtime', [MonitoringController::class, 'getRealtime']);

// ========================================================================
//                         PENGATURAN RULE
// ========================================================================

Route::post('/pengaturan/update-rule', [PengaturanController::class, 'updateRule'])->name('pengaturan.update-rule');
Route::post('/pengaturan/reset-rule', [PengaturanController::class, 'resetRule'])->name('pengaturan.reset-rule');
Route::get('/pengaturan/get-rule', [PengaturanController::class, 'getLatestRule'])->name('pengaturan.get-rule');

// ========================================================================
//                         HISTORY PREMIUM ROUTES
// ========================================================================

Route::get('/history-premium/hourly-data', [HistoryController::class, 'getHourlyData']);
Route::get('/history-premium/detail-5min', [HistoryController::class, 'getDetailPer5Menit']);
Route::get('/history-premium/week-data', [HistoryController::class, 'getWeekData']);
Route::get('/history-premium/day-detail', [HistoryController::class, 'getDayDetail']);
Route::get('/history-premium/export', [HistoryController::class, 'exportData']);

// ========================================================================
//                         KIRIM PAKAN (FEED COMMAND)
// ========================================================================

Route::post('/api/sensor/send-feed-command', [HomeController::class, 'sendFeedCommand'])->name('send.feed.command');

// ========================================================================
//                         HISTORY PREMIUM (BARU)
// ========================================================================

Route::get('/history-premium', [HistoryController::class, 'index'])->name('history.premium');
Route::get('/history-premium/week-data', [HistoryController::class, 'getWeekData'])->name('history.premium.week-data');
Route::get('/history-premium/day-detail', [HistoryController::class, 'getDayDetail'])->name('history.premium.day-detail');
Route::get('/history-premium/export', [HistoryController::class, 'exportData'])->name('history.premium.export');
Route::get('/history-premium/hourly-data', [HistoryController::class, 'getHourlyData'])->name('history.premium.hourly');
Route::get('/history-premium/detail-5min', [HistoryController::class, 'getDetailPer5Menit'])->name('history.premium.detail-5min');

// ========================================================================
//                         HISTORY LAMA (TETAP)
// ========================================================================

Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
Route::get('/history/week-data', [HistoryController::class, 'getWeekData']);
Route::get('/history/day-detail', [HistoryController::class, 'getDayDetail']);
Route::get('/history/export', [HistoryController::class, 'exportData']);

// Method asli (tetap bisa diakses)
Route::get('/history2', [HistoryController::class, 'index2'])->name('history.index2');

// ========================================================================
//                         DASHBOARD & HOME
// ========================================================================

Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/login', [LoginController::class, 'showLoginForm']);
Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
Route::get('/home', [HomeController::class, 'index']);

// ========================================================================
//                         API UNTUK DASHBOARD
// ========================================================================

Route::get('/sensor/realtime', [SensorController::class, 'realtime']);
Route::get('/api/sensor/getFeedingRecommendation', [SensorController::class, 'getFeedingRecommendation']);
Route::get('/api/profile/latest', [HomeController::class, 'getLatestProfileData']);
Route::post('/api/sensor/send-feed-command', [HomeController::class, 'sendPakan']);
Route::get('/api/feeding/today', [HomeController::class, 'getTodayFeeding']);

// ========================================================================
//                         MONITORING
// ========================================================================

Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring'); 

// API Monitoring
Route::prefix('api/monitoring')->group(function () {
    Route::get('/realtime', [MonitoringController::class, 'getRealtime']);
    Route::get('/stats', [MonitoringController::class, 'getStats']);
    Route::get('/history', [MonitoringController::class, 'getMonitoringHistory']);
    Route::get('/chart-history', [MonitoringController::class, 'getChartHistory']);
    Route::get('/feeding', [MonitoringController::class, 'getFeedingData']);
    Route::post('/feeding/store', [MonitoringController::class, 'storeFeeding']);
    Route::get('/avg-data', [MonitoringController::class, 'getMonitoringData']);
    Route::get('/avg-chart', [MonitoringController::class, 'getChartDataFromAvg']);
    Route::post('/store', [MonitoringController::class, 'storeMonitoring']);
    Route::post('/generate-dummy', [MonitoringController::class, 'generateDummyData']);
});

// ========================================================================
//                         PROFILE & BUDIDAYA
// ========================================================================

Route::prefix('profile-tambak')->group(function () {
    Route::get('/', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/biomassa/update', [ProfileController::class, 'updateBiomassa'])->name('profile.update-biomassa');
});

Route::put('/budidaya/start', [ProfileController::class, 'startBudidaya'])->name('budidaya.start');
Route::put('/budidaya/reset', [ProfileController::class, 'resetBudidaya'])->name('budidaya.reset');

// ========================================================================
//                         PENGATURAN
// ========================================================================

Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
Route::post('/pengaturan/store', [PengaturanController::class, 'store']);
Route::post('/pengaturan/rule', [PengaturanController::class, 'updateRule']);
Route::post('/pengaturan/reset', [PengaturanController::class, 'reset']);

// ========================================================================
//                         SENSOR
// ========================================================================

Route::prefix('sensor')->group(function () {
    Route::get('/realtime', [SensorController::class, 'realtime'])->name('sensor.realtime');
    Route::get('/latest', [SensorController::class, 'latest'])->name('sensor.latest');
    Route::post('/', [SensorController::class, 'store'])->name('sensor.store');
});

// ========================================================================
//                         FEEDING HISTORY
// ========================================================================

Route::get('/feeding/history', [FeedingHistoryController::class, 'index'])->name('feeding.history');

// ========================================================================
//                         REALTIME DATA
// ========================================================================

Route::get('/realtime-data', [SensorController::class, 'realtime'])->name('realtime.data');

// ========================================================================
//                         API UMUM
// ========================================================================

Route::prefix('api')->group(function () {
    // Jadwal Pengingat
    Route::get('/jadwal-list', [App\Http\Controllers\PengaturanController::class, 'getJadwalList']);
    Route::post('/jadwal-store', [App\Http\Controllers\PengaturanController::class, 'storeJadwal']);
    Route::delete('/jadwal-delete/{id}', [App\Http\Controllers\PengaturanController::class, 'deleteJadwal']);
    
    // Realtime data
    Route::get('/realtime', [HomeController::class, 'getRealtimeData'])->name('api.realtime');
    
    // Profile data
    Route::get('/latest-profile', [HomeController::class, 'getLatestProfileData']);
    
    // Pengaturan
    Route::post('/save-pengaturan', [PengaturanController::class, 'store'])->name('api.save-pengaturan');
    Route::post('/save-rule', [PengaturanController::class, 'updateRule'])->name('api.save-rule');
    
    // Kirim pakan manual
    Route::post('/send-pakan', [HomeController::class, 'sendPakan'])->name('api.send-pakan');
    
    // Feeding history
    Route::get('/feeding/today', [FeedingHistoryController::class, 'today']);
    Route::get('/feeding/weekly', [FeedingHistoryController::class, 'weekly']);
    Route::get('/feeding/monthly', [FeedingHistoryController::class, 'monthly']);
    Route::get('/feeding/all', [FeedingHistoryController::class, 'all']);
    Route::post('/feeding/manual', [FeedingHistoryController::class, 'manualFeed']);
});

// ========================================================================
//                         TESTING
// ========================================================================

Route::get('/test-wa', function () {
    $response = Http::withHeaders([
        'Authorization' => env('FONNTE_TOKEN')
    ])->post('https://api.fonnte.com/send', [
        'target' => '62895379348181',
        'message' => 'WA Gateway berhasil 🚀'
    ]);
    return $response->body();
})->name('test.wa');

Route::get('/akbar', fn() => view('welcome'))->name('welcome');