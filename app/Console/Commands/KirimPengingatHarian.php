<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PengingatJadwal;
use App\Services\FonnteService;

class KirimPengingatHarian extends Command
{
    protected $signature = 'pengingat:kirim';
    protected $description = 'Mengirim pengingat harian via WhatsApp';

 public function handle(FonnteService $fonnte)
{
    $now = now()->format('H:i');

    $jadwals = PengingatJadwal::with('pengingat')
        ->where('jam', $now)
        ->where('is_sent', false)
        ->get();

    foreach ($jadwals as $jadwal) {

        $pengingat = $jadwal->pengingat;

        $nama = $pengingat->nama_penjaga;

        $template = $pengingat->template_pesan
            ?? "👨‍🌾 Untuk Pak @{{penjaga}}\n@{{waktu}}\n@{{tanggal}}";

        $message = str_replace(
            ['@{{penjaga}}', '@{{waktu}}', '@{{tanggal}}'],
            [$nama, $jadwal->jam, now()->toDateString()],
            $template
        );

        $fonnte->sendMessage(
            $pengingat->nomor_wa,
            $message
        );

        $jadwal->update([
            'is_sent' => true
        ]);
    }
}
}