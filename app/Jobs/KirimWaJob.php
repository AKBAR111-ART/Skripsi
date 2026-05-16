<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\FonnteService;

class KirimWaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $wa;
    public $message;

    public function __construct($wa, $message)
    {
        $this->wa = $wa;
        $this->message = $message;
    }

    public function handle(FonnteService $fonnte)
    {
        $fonnte->send($this->wa, $this->message);
    }
}