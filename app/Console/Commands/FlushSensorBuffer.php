<?php
// app/Console/Commands/FlushSensorBuffer.php

namespace App\Console\Commands;  // ✅ Laravel 11 standard

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FlushSensorBuffer extends Command
{
    protected $signature = 'sensor:flush-buffer';
    protected $description = 'Flush remaining sensor data buffer to database';

    public function handle()
    {
        $buffer = Cache::get('sensor_batch_buffer', []);
        
        if (!empty($buffer)) {
            DB::table('sensors')->insert($buffer);
            Cache::forget('sensor_batch_buffer');
            $this->info('Flushed ' . count($buffer) . ' sensor records');
        } else {
            $this->info('No data to flush');
        }
    }
}