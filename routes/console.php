<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\UpdateSensor5MinAvg;
use App\Console\Commands\ResetDailyMonitoringData;
use App\Jobs\UpdateRealtimeMonitoring;
use Illuminate\Support\Facades\Log;
/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

use App\Models\JadwalPengingat;
use App\Models\PengaturanTambak;

// Schedule untuk mengirim pengingat WhatsApp setiap menit
Schedule::call(function () {
    $now = now()->format('H:i');
    $today = now()->toDateString();
    
    // Cek tanggal mulai
    $pengaturan = PengaturanTambak::first();
    if ($pengaturan && $pengaturan->tanggal && $today < $pengaturan->tanggal) {
        return; // Belum waktunya kirim
    }
    
    // Cari jadwal yang waktunya sama dengan sekarang
    $jadwals = JadwalPengingat::where('jam', $now)
        ->where('is_sent', false)
        ->get();
    
    foreach ($jadwals as $jadwal) {
        // Ganti template dengan waktu
        $pesan = str_replace('{{waktu}}', $jadwal->jam, $jadwal->pesan);
        
        // Kirim WhatsApp
        $jadwal->send();
        
        Log::info("Reminder sent for schedule {$jadwal->jam}");
    }
})->everyMinute();
// Schedule untuk agregasi data sensor setiap 5 menit
Schedule::command('sensor:aggregate')->everyFiveMinutes();

// Schedule untuk clean data lama setiap hari
Schedule::command('sensor:clean')->daily();

// Contoh schedule lain (opsional)
Schedule::call(function () {
    Artisan::call('cache:prune-stale-tags');
})->hourly();

// Reset data setiap hari jam 00:05 (untuk hari baru)
Schedule::command('daily:reset-monitoring-data')
    ->dailyAt('00:05')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/daily-reset.log'));

// Update realtime setiap 5 menit
Schedule::job(new UpdateRealtimeMonitoring())
    ->everyFiveMinutes()
    ->withoutOverlapping();

// Jalankan command manual jika perlu (setiap jam untuk keamanan)
Schedule::command('daily:reset-monitoring-data')
    ->hourly()
    ->when(function () {
        // Hanya jalankan jika hari sudah berganti
        $lastRun = cache('last_reset_date');
        $today = now()->toDateString();
        if ($lastRun != $today) {
            cache(['last_reset_date' => $today], now()->addDay());
            return true;
        }
        return false;
    });

// Jalankan setiap hari jam 00:05 untuk menghitung data kemarin
Schedule::command('sensor:calculate-5min-avg')
    ->dailyAt('00:05')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/sensor-5min-avg.log'));

// Update data realtime setiap 5 menit
Schedule::job(new UpdateSensor5MinAvg())
    ->everyFiveMinutes()
    ->withoutOverlapping();

// Jika ingin menggunakan command untuk update realtime
// Schedule::command('sensor:update-realtime')->everyFiveMinutes();
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ================================================================
// ⚠️ PERINGATAN ⚠️
// ================================================================
// SEMUA SCHEDULE (jadwal) SUDAH didefinisikan di bootstrap/app.php
// Jangan tambahkan Schedule::command() APAPUN di file ini!
// Jika ditambahkan, akan terjadi DUPLIKASI jadwal.
// ================================================================