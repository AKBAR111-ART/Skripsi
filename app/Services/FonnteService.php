<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    /**
     * =========================
     * KIRIM WHATSAPP VIA FONNTE
     * =========================
     */
    public function send($target, $message)
    {
        // =====================
        // VALIDASI
        // =====================
        if (empty($target) || empty($message)) {
            return [
                'status' => false,
                'message' => 'Target atau pesan kosong'
            ];
        }

        // =====================
        // FORMAT NOMOR TELEPON
        // =====================
        $target = preg_replace('/[^0-9]/', '', $target);

        if (str_starts_with($target, '0')) {
            $target = preg_replace('/^0/', '62', $target);
        }

        // =====================
        // FORMAT PESAN
        // =====================
        $message = trim($message);
        $message = str_replace(["\r\n", "\r"], "\n", $message);
        $message = preg_replace("/\n{3,}/", "\n\n", $message);

        try {
            // =====================
            // REQUEST KE FONNTE API
            // =====================
            $response = Http::asForm()
                ->withHeaders([
                    'Authorization' => config('services.fonnte.token'),
                ])
                ->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                ]);

            // =====================
            // LOG RESPONSE
            // =====================
            Log::info('FONNTE RESPONSE', [
                'target' => $target,
                'message' => $message,
                'status_code' => $response->status(),
                'response' => $response->json(),
            ]);

            return $response->json();
        } catch (\Exception $e) {

            // =====================
            // LOG ERROR
            // =====================
            Log::error('FONNTE ERROR', [
                'target' => $target,
                'message' => $message,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    public function sendMessage($target, $message)
{
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'target' => $target,
            'message' => $message,
        ],
        CURLOPT_HTTPHEADER => [
            'Authorization: YOUR_TOKEN_FONNTE'
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    return $response;
}
}