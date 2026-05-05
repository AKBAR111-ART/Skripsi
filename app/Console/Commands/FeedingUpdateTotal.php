<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FeedingDaily;

class FeedingUpdateTotal extends Command
{
    protected $signature = 'feeding:update-total';
    protected $description = 'Update total feeding';

   public function handle(): void
{
    $data = \App\Models\FeedingDaily::where('tanggal', now()->toDateString())->first();

    if (!$data) {
        $this->error('Data hari ini belum ada!');
        return;
    }

    $totalRekom = 
        ($data->rekomendasi_pagi ?? 0) +
        ($data->rekomendasi_siang ?? 0) +
        ($data->rekomendasi_sore ?? 0) +
        ($data->rekomendasi_malam ?? 0);

    $totalReal = 
        ($data->real_pakan_pagi ?? 0) +
        ($data->real_pakan_siang ?? 0) +
        ($data->real_pakan_sore ?? 0) +
        ($data->real_pakan_malam ?? 0);

    $data->update([
        'total_rekomendasi' => $totalRekom,
        'total_real' => $totalReal
    ]);

    $this->info('✅ Total feeding berhasil diupdate');
}
}
