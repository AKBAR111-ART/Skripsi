<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sensor;
use App\Models\Sensor5Min;

class Aggregate5Min extends Command
{
    protected $signature = 'app:aggregate5min';
    protected $description = 'Aggregate sensor data every 5 minutes';

public function handle()
{  $this->info("Command aggregation 5 menit dijalankan");
    $end = now();
    $start = now()->subMinutes(5);

    // ambil data 5 menit terakhir
    $data = Sensor::whereBetween('created_at', [$start, $end])->get();

    if ($data->count() == 0) {
        return;
    }

    // hitung rata-rata
    $avgPh = $data->avg('ph');
    $avgTurbidity = $data->avg('turbidity');

    // simpan ke tabel 5 menit
    Sensor5Min::create([
        'avg_ph' => $avgPh,
        'avg_turbidity' => $avgTurbidity,
        'time_5min' => $start
    ]);
      

    // proses kamu di sini
}
}