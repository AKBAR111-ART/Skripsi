<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sensor;
use App\Models\Sensor10Min;

class Aggregate10Min extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:aggregate10min';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();
    $start = $now->copy()->subMinutes(10);

    $data = Sensor::whereBetween('created_at', [$start, $now])->get();

    if ($data->count() == 0) return;

    Sensor10Min::create([
        'avg_ph' => $data->avg('ph'),
        'avg_turbidity' => $data->avg('turbidity'),
        'time_10min' => $now
    ]);
    }
}
