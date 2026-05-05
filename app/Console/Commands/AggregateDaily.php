<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SensorDaily;
use App\Models\Sensor5Min;

class AggregateDaily extends Command
{
    protected $signature = 'app:aggregatedaily';
    protected $description = 'Aggregate data harian';

    public function handle()
    {
        $today = now()->toDateString();

        // FIX: ganti time_10min → time_5min
        $data = Sensor5Min::whereDate('time_5min', $today)->get();

        // 🔥 TAMBAHAN DEBUG
        $this->info("Jumlah data ditemukan: " . $data->count());

        if ($data->count() == 0) {
            $this->info('Tidak ada data');
            return;
        }

        SensorDaily::updateOrCreate(
            ['date' => $today],
            [
                'avg_ph' => $data->avg('avg_ph'),
                'avg_turbidity' => $data->avg('avg_turbidity'),
            ]
        );

        $this->info('Data harian berhasil disimpan');
    }
}