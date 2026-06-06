<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    protected $apiKey;
    protected $city;

    public function __construct()
    {
        $this->apiKey = env('OPENWEATHER_API_KEY');
        $this->city = 'Jakarta'; // Bisa disesuaikan dengan lokasi tambak
    }

    public function getWeather()
    {
        return Cache::remember('weather_data', 1800, function () {
            try {
                $response = Http::get("https://api.openweathermap.org/data/2.5/weather", [
                    'q' => $this->city,
                    'appid' => $this->apiKey,
                    'units' => 'metric'
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $weatherMain = $data['weather'][0]['main'] ?? 'Clear';
                    
                    // Mapping ke format Indonesia
                    $cuaca = match($weatherMain) {
                        'Clear' => 'Cerah',
                        'Clouds' => 'Berawan',
                        'Rain', 'Drizzle' => 'Hujan',
                        default => $weatherMain
                    };
                    
                    return [
                        'cuaca' => $cuaca,
                        'intensitas_hujan' => $data['rain']['1h'] ?? 0,
                        'suhu' => $data['main']['temp'],
                        'kelembaban' => $data['main']['humidity'],
                        'keterangan' => $data['weather'][0]['description'],
                        'last_update' => now()
                    ];
                }
                
                return $this->getDefaultWeather();
                
            } catch (\Exception $e) {
                Log::error("Weather API Error: " . $e->getMessage());
                return $this->getDefaultWeather();
            }
        });
    }
    
    private function getDefaultWeather()
    {
        return [
            'cuaca' => 'Cerah',
            'intensitas_hujan' => 0,
            'suhu' => 28,
            'kelembaban' => 70,
            'keterangan' => 'Data default (API error)',
            'last_update' => now()
        ];
    }
}