<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class JadwalPengingat extends Model
{
    // Pastikan tabel yang benar
    protected $table = 'jadwal_pengingat';
    
    protected $fillable = [
        'jam',
        'pesan',
        'target_nomor',
        'is_sent'
    ];
    
    protected $casts = [
        'target_nomor' => 'array',
        'is_sent' => 'boolean'
    ];
    
    protected $dates = [
        'created_at',
        'updated_at'
    ];
    
    /**
     * Get formatted target nomor untuk ditampilkan
     */
    public function getTargetNomorFormattedAttribute()
    {
        if (is_array($this->target_nomor)) {
            return implode(', ', $this->target_nomor);
        }
        return $this->target_nomor;
    }
    
    /**
     * Kirim pesan ke semua nomor target
     */
    public function send()
    {
        $nomors = is_array($this->target_nomor) ? $this->target_nomor : [$this->target_nomor];
        $success = true;
        
        foreach ($nomors as $nomor) {
            $result = $this->sendToWhatsApp($nomor, $this->pesan);
            if (!$result) $success = false;
        }
        
        if ($success) {
            $this->update(['is_sent' => true]);
        }
        
        return $success;
    }
    
    /**
     * Kirim ke WhatsApp via Fonnte API
     */
    private function sendToWhatsApp($nomor, $pesan)
    {
        $apiKey = env('FONNTE_API_KEY', '');
        
        if (empty($apiKey)) {
            Log::warning('Fonnte API Key not set');
            return false;
        }
        
        // Format nomor (pastikan tidak ada spasi)
        $nomor = preg_replace('/[^0-9]/', '', $nomor);
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => array(
                'target' => $nomor,
                'message' => $pesan,
            ),
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $apiKey
            ),
        ));
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        Log::info("WhatsApp API Response: HTTP {$httpCode} - " . $response);
        
        return $httpCode == 200;
    }
}