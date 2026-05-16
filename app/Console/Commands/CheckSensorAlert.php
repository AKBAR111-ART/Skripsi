<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SensorAlertService;

class CheckSensorAlert extends Command
{
    protected $signature = 'sensor:check';
    protected $description = 'Check sensor dan kirim WA alert otomatis';

    public function handle()
    {
        // SIMULASI DATA SENSOR (nanti dari IoT / DB)
        $sensor = [
            'ph' => rand(60, 90) / 10,
            'turbidity' => rand(20, 100),
        ];

        $service = new SensorAlertService();
        $service->sendAlert($sensor);

        $this->info("Sensor checked & alert processed");
    }
}
