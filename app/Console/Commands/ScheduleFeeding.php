<?php

namespace App\Console\Commands;

use App\Models\FeedingRecord;
use App\Models\TambakProfile;
use App\Models\RuleSensor;
use App\Models\SensorRealtime;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduleFeeding extends Command
{
    protected $signature = 'feeding:schedule';
    protected $description = 'Jadwal pakan otomatis 3x sehari (pagi, siang, sore)';

    public function handle()
    {
        $now = Carbon::now();
        $jam = (int)$now->format('H');
        
        // Tentukan jadwal berdasarkan jam
        $jadwal = null;
        $waktuTarget = null;
        
        if ($jam >= 6 && $jam < 10) {
            $jadwal = 'pagi';
            $waktuTarget = '06:00:00';
        } elseif ($jam >= 11 && $jam < 14) {
            $jadwal = 'siang';
            $waktuTarget = '11:00:00';
        } elseif ($jam >= 16 && $jam < 19) {
            $jadwal = 'sore';
            $waktuTarget = '16:00:00';
        } else {
            // Bukan jam pakan
            return;
        }
        
        // Cek apakah sudah memberi pakan di jadwal ini
        $sudahDiberi = FeedingRecord::whereDate('created_at', today())
            ->where('jadwal', $jadwal)
            ->exists();
        
        if ($sudahDiberi) {
            $this->info("Pakan jadwal {$jadwal} sudah diberikan hari ini");
            return;
        }
        
        // Ambil data profile
        $profile = TambakProfile::first();
        if (!$profile || !$profile->populasi) {
            $this->error("Data populasi belum diisi");
            return;
        }
        
        // Hitung umur
        $umurMinggu = 1;
        if ($profile->tanggal_mulai_budidaya) {
            $start = Carbon::parse($profile->tanggal_mulai_budidaya);
            $umurHari = $start->diffInDays(now());
            $umurMinggu = max(1, ceil($umurHari / 7));
        }
        
        // Hitung pakan per ekor berdasarkan umur
        $pakanPerEkor = $this->getPakanPerEkor($umurMinggu);
        $totalPakanGram = $profile->populasi * $pakanPerEkor;
        
        // Bagi 3 untuk 3x sehari
        $pakanPerJadwalGram = round($totalPakanGram / 3);
        $pakanPerJadwalKg = round($pakanPerJadwalGram / 1000, 2);
        
        // Ambil data sensor saat ini
        $sensor = SensorRealtime::first();
        $rule = RuleSensor::first();
        
        // Tentukan status air
        $phStatus = $this->getPhStatus($sensor->ph ?? 7, $rule);
        $turbidityStatus = $this->getTurbidityStatus($sensor->turbidity ?? 30, $rule);
        
        // Faktor koreksi
        $faktor = 1.0;
        $statusKeseluruhan = 'aman';
        
        if ($phStatus === 'bahaya' || $turbidityStatus === 'bahaya') {
            $faktor = 0;
            $statusKeseluruhan = 'bahaya';
        } elseif ($phStatus === 'peringatan' || $turbidityStatus === 'peringatan') {
            $faktor = 0.5;
            $statusKeseluruhan = 'peringatan';
        }
        
        // Hitung pakan setelah koreksi
        $pakanFinalGram = round($pakanPerJadwalGram * $faktor);
        $pakanFinalKg = round($pakanFinalGram / 1000, 2);
        
        // Simpan record pakan
        $record = FeedingRecord::create([
            'pakan_gram' => $pakanFinalGram,
            'pakan_kg' => $pakanFinalKg,
            'jadwal' => $jadwal,
            'waktu_pemberian' => $waktuTarget,
            'status' => 'auto',
            'keterangan' => "Pakan otomatis jadwal {$jadwal} (kondisi air: {$statusKeseluruhan})",
            'ph_saat_pemberian' => $sensor->ph ?? null,
            'turbidity_saat_pemberian' => $sensor->turbidity ?? null,
            'status_air_saat_pemberian' => $statusKeseluruhan,
            'tambak_profile_id' => $profile->id
        ]);
        
        $this->info("✅ Pakan jadwal {$jadwal} berhasil: {$pakanFinalKg} kg ({$pakanFinalGram} gram)");
        
        // Log ke file
        Log::info("Feeding scheduled: {$jadwal}, amount: {$pakanFinalGram} gram, status: {$statusKeseluruhan}");
    }
    
    private function getPakanPerEkor($umurMinggu)
    {
        $tabel = [
            1 => 0.5, 2 => 1.0, 3 => 2.0, 4 => 3.0,
            5 => 4.5, 6 => 6.0, 7 => 8.0, 8 => 10.0,
            9 => 12.5, 10 => 15.0, 11 => 18.0, 12 => 21.0, 13 => 25.0,
        ];
        return $tabel[$umurMinggu] ?? (25.0 + (($umurMinggu - 13) * 3.5));
    }
    
    private function getPhStatus($ph, $rule)
    {
        if (!$rule) return 'aman';
        if ($ph < $rule->ph_danger_low || $ph > $rule->ph_danger_high) return 'bahaya';
        if ($ph < $rule->ph_min_good || $ph > $rule->ph_max_good) return 'peringatan';
        return 'aman';
    }
    
    private function getTurbidityStatus($turbidity, $rule)
    {
        if (!$rule) return 'aman';
        if ($turbidity < $rule->turbidity_danger_low || $turbidity > $rule->turbidity_danger_high) return 'bahaya';
        if ($turbidity < $rule->turbidity_min_good || $turbidity > $rule->turbidity_max_good) return 'peringatan';
        return 'aman';
    }
}