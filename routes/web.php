<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UtamaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\ProfileController;
Route::get('/akbar', function () {
    return view('welcome');
});


Route::get('/', [UtamaController::class, 'index']);
Route::get('/home', [HomeController::class, 'index1']);
Route::get('/history', [HistoryController::class, 'index2']);
Route::get('/monitoring', [MonitoringController::class, 'index3']);
Route::get('/pengaturan', [PengaturanController::class, 'index4']);
Route::get('/profile', [ProfileController::class, 'index5']);