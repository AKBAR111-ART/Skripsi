<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SensorDaily;
use App\Models\SensorMonthly;

class AggregateMonthly extends Command
{
    protected $signature = 'app:aggregatemonthly';
    protected $description = 'Aggregate data bulanan';

    public function handle()
    {
        $month = now()->format('Y-m');

        $data = SensorDaily::whereRaw("to_char(date, 'YYYY-MM') = ?", [$month])->get();

        if ($data->count() == 0) return;

        SensorMonthly::updateOrCreate(
            ['month' => $month],
            [
                'avg_ph' => $data->avg('avg_ph'),
                'avg_turbidity' => $data->avg('avg_turbidity'),
            ]
        );

        $this->info('Data bulanan berhasil di-aggregate');
    }
}