<?php
use Illuminate\Console\Command;
use App\Models\Sensor5Min;
use App\Models\SensorDaily;

class AggregateDaily extends Command
{
    protected $signature = 'app:aggregatedaily';
    protected $description = 'Aggregate sensor data per day';

    public function handle()
    {
        $today = now()->toDateString();

        $data = Sensor5Min::whereDate('time_5min', $today)->get();

        if ($data->count() > 0) {

            $avgPh = $data->avg('avg_ph');
            $avgTurbidity = $data->avg('avg_turbidity');

            SensorDaily::updateOrCreate(
                ['date' => $today],
                [
                    'avg_ph' => $avgPh,
                    'avg_turbidity' => $avgTurbidity
                ]
            );
        }
    }
}