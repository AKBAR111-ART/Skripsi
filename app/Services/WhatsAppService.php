<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $token;
    protected $apiUrl;

    public function __construct()
    {
        $this->token = env('FONNTE_TOKEN');
        $this->apiUrl = 'https://api.fonnte.com/send';
    }

    /**
     * Kirim pesan WhatsApp ke satu nomor
     */
    public function sendMessage($target, $message, $jenis = 'umum')
    {
        if (!$this->token) {
            Log::warning('FONNTE_TOKEN tidak ditemukan di .env');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token
            ])->post($this->apiUrl, [
                'target' => $target,
                'message' => $message
            ]);

            $result = $response->json();

            if (isset($result['status']) && $result['status'] === true) {
                Log::info("WA Terkirim ke {$target}: {$jenis}");
                return true;
            }

            Log::error("WA Gagal: " . json_encode($result));
            return false;

        } catch (\Exception $e) {
            Log::error("WA Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim pesan ke multiple nomor
     */
    public function sendMultiple($targets, $message, $jenis = 'umum')
    {
        $success = 0;
        foreach ($targets as $target) {
            if ($this->sendMessage($target, $message, $jenis)) {
                $success++;
            }
        }
        return $success;
    }
}