<?php

namespace App\Console\Commands;

use App\Models\PengingatJadwal;
use App\Models\Pengaturan;
use App\Models\SensorRealtime;
use App\Models\RuleSensor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class KirimPengingatJadwal extends Command
{
    protected $signature = 'pengingat:jadwal';
    protected $description = 'Kirim pengingat WhatsApp sesuai jadwal';

    public function handle()
    {
        $this->info('=== MEMERIKSA JADWAL PENGINGAT ===');
        
        $jadwalList = PengingatJadwal::where('is_sent', false)->get();
        
        $this->info('Jumlah jadwal pending: ' . $jadwalList->count());
        
        if ($jadwalList->isEmpty()) {
            $this->info('Tidak ada jadwal.');
            return;
        }
        
        $pengaturan = Pengaturan::first();
        $token = env('FONNTE_TOKEN');
        
        if (!$token) {
            $this->error('FONNTE_TOKEN tidak ditemukan!');
            return;
        }
        
        $nomorWa = json_decode($pengaturan->nomor_wa ?? '[]', true);
        $target = !empty($nomorWa) ? $nomorWa[0] : '62895379348181';
        
        $sensor = SensorRealtime::first();
        $rule = RuleSensor::first();
        
        $phStatus = $this->getStatusPh($sensor->ph ?? 7, $rule);
        $turbidityStatus = $this->getStatusTurbidity($sensor->turbidity ?? 30, $rule);
        $rekomendasi = $this->getRekomendasi($phStatus, $turbidityStatus);
        
        $templateMonitoring = $pengaturan->template_pesan ?? "🌊 *MONITORING TAMBAK* 🌊\n\n📊 *Data Sensor Terkini:*\n💧 pH: {{ph}} ({{ph_status}})\n🌊 Turbidity: {{turbidity}} NTU ({{turbidity_status}})\n\n⚠️ *Rekomendasi:*\n{{rekomendasi}}";
        
        $pesanMonitoring = str_replace(
            ['{{ph}}', '{{ph_status}}', '{{turbidity}}', '{{turbidity_status}}', '{{rekomendasi}}'],
            [$sensor->ph ?? '?', $phStatus, $sensor->turbidity ?? '?', $turbidityStatus, $rekomendasi],
            $templateMonitoring
        );
        
        $sent = 0;
        $now = now();
        $currentTime = $now->format('H:i:s');
        
        foreach ($jadwalList as $jadwal) {
            $jamJadwal = substr($jadwal->jam, 0, 5);
            $jamSekarang = substr($currentTime, 0, 5);
            
            if ($jamJadwal <= $jamSekarang) {
                $pesan = "🌊 *PENGINGAT TAMBAK* 🌊\n\n";
                $pesan .= "⏰ *Jadwal:* {$jadwal->jam}\n";
                $pesan .= "💬 *Pesan:* {$jadwal->pesan}\n\n────────────────────\n\n";
                $pesan .= $pesanMonitoring;
                
                try {
                    $response = Http::withHeaders(['Authorization' => $token])
                        ->post('https://api.fonnte.com/send', ['target' => $target, 'message' => $pesan]);
                    
                    $result = $response->json();
                    
                    if (isset($result['status']) && $result['status'] === true) {
                        $sent++;
                        $this->info("✅ WA BERHASIL dikirim ke {$target}");
                    } else {
                        $this->error("❌ WA GAGAL");
                    }
                } catch (\Exception $e) {
                    $this->error("❌ ERROR: " . $e->getMessage());
                }
                
                $jadwal->is_sent = true;
                $jadwal->save();
            }
        }
        
        $this->info("Selesai: {$sent} WA terkirim");
    }
    
    private function getStatusPh($ph, $rule)
    {
        if (!$rule) return 'normal';
        if ($ph < $rule->ph_danger_low || $ph > $rule->ph_danger_high) return 'bahaya';
        if ($ph < $rule->ph_min_good || $ph > $rule->ph_max_good) return 'peringatan';
        return 'baik';
    }
    
    private function getStatusTurbidity($turbidity, $rule)
    {
        if (!$rule) return 'normal';
        if ($turbidity < $rule->turbidity_danger_low || $turbidity > $rule->turbidity_danger_high) return 'bahaya';
        if ($turbidity < $rule->turbidity_min_good || $turbidity > $rule->turbidity_max_good) return 'peringatan';
        return 'baik';
    }
    
    private function getRekomendasi($phStatus, $turbidityStatus)
    {
        if ($phStatus === 'bahaya' || $turbidityStatus === 'bahaya') return "🔴 KONDISI BAHAYA! Hentikan pakan!";
        if ($phStatus === 'peringatan' || $turbidityStatus === 'peringatan') return "🟡 PERINGATAN! Kurangi pakan 50%!";
        return "✅ Kondisi air normal. Pakan optimal.";
    }
}