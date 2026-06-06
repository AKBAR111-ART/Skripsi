<?php

namespace App\Console\Commands;

use App\Models\JadwalPengingat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class KirimPengingatJadwal extends Command
{
    protected $signature = 'pengingat:jadwal';
    protected $description = 'Kirim pengingat jadwal WhatsApp';

    public function handle()
    {
        $now = now()->format('H:i');
        
        $jadwalList = JadwalPengingat::where('jam', $now)
            ->where('is_sent', false)
            ->get();
        
        foreach ($jadwalList as $jadwal) {
            $jadwal->send();
            $this->info("Jadwal {$jadwal->jam} dikirim ke " . implode(', ', $jadwal->target_nomor));
        }
        
        return Command::SUCCESS;
    }
}