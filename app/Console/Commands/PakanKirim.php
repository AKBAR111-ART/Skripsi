<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PengaturanTambak;
use Carbon\Carbon;
use App\Services\FonnteService;

class PakanKirim extends Command
{
    // =========================
    // COMMAND
    // =========================

    protected $signature = 'pakan:kirim';

    protected $description = 'Kirim WhatsApp otomatis sesuai jadwal';

    // =========================
    // HANDLE
    // =========================

    public function handle()
    {
        // =====================
        // AMBIL DATA
        // =====================

        $setting = PengaturanTambak::first();

        if (!$setting) {

            $this->info('Data pengaturan tidak ditemukan');
            return;
        }

        $this->info('DATA DITEMUKAN');

        // =====================
        // AMBIL JADWAL
        // =====================

        $jadwalDb = $setting->jadwal_kirim;

        if (!$jadwalDb) {

            $this->info('Jadwal kosong');
            return;
        }

        // =====================
        // HANDLE FORMAT ARRAY
        // =====================

        if (is_string($jadwalDb) && str_contains($jadwalDb, '[')) {

            $decoded = json_decode($jadwalDb, true);

            $jadwalDb = $decoded[0] ?? null;
        }

        // =====================
        // FORMAT JADWAL
        // =====================

        try {

            $jadwal = Carbon::parse($jadwalDb);

        } catch (\Exception $e) {

            $this->info('Format jadwal error');
            return;
        }

        $now = now();

        $this->info("NOW     : " . $now->format('Y-m-d H:i'));
        $this->info("JADWAL  : " . $jadwal->format('Y-m-d H:i'));

        // =====================
        // CEK WAKTU LEWAT
        // =====================

        if ($jadwal->lt($now->copy()->subMinute())) {

            $this->info('Jadwal sudah lewat');

            // reset supaya tidak dikirim lagi
            $setting->update([
                'jadwal_kirim' => null
            ]);

            return;
        }

        // =====================
        // CEK SESUAI MENIT
        // =====================

        if ($now->format('Y-m-d H:i') !== $jadwal->format('Y-m-d H:i')) {

            $this->info('Belum waktunya kirim');
            return;
        }

        // =====================
        // NOMOR WA
        // =====================

        $nomorWa = is_array($setting->nomor_wa)
            ? $setting->nomor_wa
            : json_decode($setting->nomor_wa, true);

        // =====================
        // PENJAGA
        // =====================

        $penjaga = is_array($setting->penjaga)
            ? $setting->penjaga
            : json_decode($setting->penjaga, true);

        // =====================
        // VALIDASI
        // =====================

        if (empty($nomorWa)) {

            $this->info('Nomor WA kosong');
            return;
        }

        // =====================
        // LOOP KIRIM
        // =====================

        foreach ($nomorWa as $index => $wa) {

            // =====================
            // FORMAT NOMOR
            // =====================

            $wa = preg_replace('/^0/', '62', $wa);

            $namaPenjaga = $penjaga[$index] ?? 'Penjaga';

            // =====================
            // TEMPLATE DEFAULT
            // =====================

            $message = "
🚀 PAKAN TAMBAK

Halo {$namaPenjaga}

📅 Tanggal : " . $now->format('d-m-Y') . "
⏰ Waktu   : " . $now->format('H:i') . "

Segera lakukan pengecekan dan pemberian pakan tambak.
";

            // =====================
            // TEMPLATE CUSTOM
            // =====================

            if (!empty($setting->template_pesan)) {

                $template = $setting->template_pesan;

                // replace variable
                $template = str_replace(
                    [
                        '{{penjaga}}',
                        '{{waktu}}',
                        '{{tanggal}}'
                    ],
                    [
                        $namaPenjaga,
                        $now->format('H:i'),
                        $now->format('d-m-Y')
                    ],
                    $template
                );

                // rapikan enter
                $template = trim($template);

                $template = preg_replace("/\r\n|\r/", "\n", $template);

                $template = preg_replace("/\n{3,}/", "\n\n", $template);

                // gabungkan pesan
                $message .= "\n";
                $message .= "📝 Catatan Tambahan\n";
                $message .= $template;
            }

            // =====================
            // DEBUG
            // =====================

            $this->info('======================');
            $this->info("TARGET : {$wa}");
            $this->info($message);

            // =====================
            // KIRIM WA
            // =====================

            try {

                $response = app(FonnteService::class)
                    ->send($wa, $message);

                $this->info(json_encode($response));

                $this->info("WA berhasil terkirim ke {$wa}");

            } catch (\Exception $e) {

                $this->error("Gagal kirim WA ke {$wa}");
                $this->error($e->getMessage());
            }
        }

        // =====================
        // RESET JADWAL
        // =====================

        $setting->update([
            'jadwal_kirim' => null
        ]);

        $this->info('SELESAI');
    }
}