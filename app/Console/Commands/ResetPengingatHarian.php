<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PengingatJadwal;

class ResetPengingatHarian extends Command
{
    protected $signature = 'pengingat:reset';
    protected $description = 'Reset status is_sent pengingat harian setiap hari';

    public function handle()
    {
        PengingatJadwal::query()->update([
            'is_sent' => false
        ]);

        $this->info('Semua pengingat berhasil di-reset');
    }
}