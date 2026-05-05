<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FeedingDaily;

class FeedingCreateDaily extends Command
{
    protected $signature = 'feeding:daily-create';
    protected $description = 'Create daily feeding row';

    public function handle(): void
    {
        FeedingDaily::firstOrCreate([
            'tanggal' => now()->toDateString()
        ]);
    }
}

