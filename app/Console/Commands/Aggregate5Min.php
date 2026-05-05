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
{
    $from = now()->subMinutes(5);
    $to = now();

    $data = Sensor::whereBetween('created_at', [$from, $to])->get();

    echo "DATA COUNT: " . $data->count() . "\n";

    if ($data->count() > 0) {

        $avgPh = $data->avg('ph');
        $avgTurbidity = $data->avg('turbidity');

        Sensor5Min::create([
            'avg_ph' => $avgPh,
            'avg_turbidity' => $avgTurbidity,
            'time_5min' => now(),
        ]);

        echo "INSERT SUCCESS\n";
    } else {
        echo "NO DATA\n";
    }
}
}