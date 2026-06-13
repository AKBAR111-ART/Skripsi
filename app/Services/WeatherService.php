<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    protected $apiKey;
    protected $defaultLat;
    protected $defaultLon;
    protected $defaultCity;

    // Koordinat Sumenep - Desa Nambakor
    const SUMENEP_LAT = -7.0087;
    const SUMENEP_LON = 113.8662;
    const DEFAULT_CITY = 'Sumenep';
    const CACHE_DURATION = 1800; // 30 menit

    public function __construct()
    {
        $this->apiKey = env('OPENWEATHER_API_KEY');
        $this->defaultLat = env('OPENWEATHER_DEFAULT_LAT', self::SUMENEP_LAT);
        $this->defaultLon = env('OPENWEATHER_DEFAULT_LON', self::SUMENEP_LON);
        $this->defaultCity = env('OPENWEATHER_DEFAULT_CITY', self::DEFAULT_CITY);
    }

    /**
     * Get weather by city name (untuk Sumenep)
     */
    public function getWeather($city = null)
    {
        $city = $city ?? $this->defaultCity;
        $cacheKey = "weather_{$city}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($city) {
            try {
                $response = Http::timeout(10)->get("https://api.openweathermap.org/data/2.5/weather", [
                    'q' => $city,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang' => 'id'
                ]);

                if ($response->successful()) {
                    return $this->formatWeatherData($response->json(), $city);
                }

                Log::warning("Weather API failed for city: {$city}", [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return $this->getDefaultWeather();

            } catch (\Exception $e) {
                Log::error("Weather API Error: " . $e->getMessage());
                return $this->getDefaultWeather();
            }
        });
    }

    /**
     * Get weather by coordinates (lebih akurat untuk Desa Nambakor)
     */
    public function getWeatherByCoordinates($lat = null, $lon = null)
    {
        $lat = $lat ?? $this->defaultLat;
        $lon = $lon ?? $this->defaultLon;
        $cacheKey = "weather_coord_{$lat}_{$lon}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($lat, $lon) {
            try {
                $response = Http::timeout(10)->get("https://api.openweathermap.org/data/2.5/weather", [
                    'lat' => $lat,
                    'lon' => $lon,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang' => 'id'
                ]);

                if ($response->successful()) {
                    return $this->formatWeatherData($response->json(), "Desa Nambakor, Sumenep");
                }

                return $this->getWeather(); // fallback ke city

            } catch (\Exception $e) {
                Log::error("Weather API Error (coordinates): " . $e->getMessage());
                return $this->getDefaultWeather();
            }
        });
    }

    /**
     * Get weather for Sumenep (Nambakor) - Method utama
     */
    public function getWeatherSumenep()
    {
        return $this->getWeatherByCoordinates(self::SUMENEP_LAT, self::SUMENEP_LON);
    }

    /**
     * Get weather based on user's tambak location
     */
    public function getWeatherForTambak($user = null)
    {
        // Jika user memiliki koordinat tersimpan
        if ($user && isset($user->latitude) && isset($user->longitude)) {
            return $this->getWeatherByCoordinates($user->latitude, $user->longitude);
        }

        // Jika user memiliki lokasi tambak, cek apakah mengandung Sumenep/Nambakor
        if ($user && $user->lokasi_tambak) {
            $location = strtolower($user->lokasi_tambak);
            if (str_contains($location, 'sumenep') || str_contains($location, 'nambakor')) {
                return $this->getWeatherSumenep();
            }
        }

        // Default ke Sumenep
        return $this->getWeatherSumenep();
    }

    /**
     * Format weather data for display
     */
    private function formatWeatherData($data, $locationName = null)
    {
        $weatherMain = $data['weather'][0]['main'] ?? 'Clear';
        $weatherId = $data['weather'][0]['id'] ?? 800;
        
        // Mapping cuaca ke Bahasa Indonesia yang lebih detail
        $cuaca = $this->mapWeatherToIndonesian($weatherMain, $weatherId);
        
        // Hitung rekomendasi pakan berdasarkan cuaca
        $feedRecommendation = $this->calculateFeedRecommendation($cuaca, $weatherMain);
        
        return [
            'success' => true,
            'location' => $locationName ?? $data['name'] ?? 'Desa Nambakor, Sumenep',
            'cuaca' => $cuaca,
            'cuaca_en' => $weatherMain,
            'intensitas_hujan' => $data['rain']['1h'] ?? $data['rain']['3h'] ?? 0,
            'suhu' => round($data['main']['temp'] ?? 0, 1),
            'suhu_min' => round($data['main']['temp_min'] ?? 0, 1),
            'suhu_max' => round($data['main']['temp_max'] ?? 0, 1),
            'kelembaban' => $data['main']['humidity'] ?? 0,
            'tekanan' => $data['main']['pressure'] ?? 0,
            'kecepatan_angin' => round($data['wind']['speed'] ?? 0, 1),
            'keterangan' => $data['weather'][0]['description'] ?? '',
            'icon' => $data['weather'][0]['icon'] ?? '01d',
            'feed_recommendation' => $feedRecommendation,
            'last_update' => now()->toIso8601String(),
        ];
    }

    /**
     * Map weather condition to Indonesian
     */
    private function mapWeatherToIndonesian($weatherMain, $weatherId)
    {
        // Mapping berdasarkan weather ID (lebih akurat)
        if ($weatherId >= 200 && $weatherId < 300) {
            return 'Badai Petir';
        } elseif ($weatherId >= 300 && $weatherId < 400) {
            return 'Gerimis';
        } elseif ($weatherId >= 500 && $weatherId < 600) {
            if ($weatherId >= 500 && $weatherId < 505) return 'Hujan Ringan';
            if ($weatherId >= 505 && $weatherId < 520) return 'Hujan';
            return 'Hujan Lebat';
        } elseif ($weatherId >= 600 && $weatherId < 700) {
            return 'Salju';
        } elseif ($weatherId >= 700 && $weatherId < 800) {
            return 'Kabut';
        } elseif ($weatherId == 800) {
            return 'Cerah';
        } elseif ($weatherId == 801) {
            return 'Berawan Sebagian';
        } elseif ($weatherId == 802) {
            return 'Berawan';
        } elseif ($weatherId == 803 || $weatherId == 804) {
            return 'Mendung';
        }
        
        // Fallback
        return match($weatherMain) {
            'Clear' => 'Cerah',
            'Clouds' => 'Berawan',
            'Rain', 'Drizzle' => 'Hujan',
            'Thunderstorm' => 'Badai',
            default => $weatherMain
        };
    }

    /**
     * Calculate feed recommendation based on weather
     */
    private function calculateFeedRecommendation($cuaca, $weatherMain)
    {
        if (str_contains($cuaca, 'Hujan') || $weatherMain === 'Rain') {
            return [
                'status' => 'warning',
                'message' => 'Hujan terdeteksi! Kurangi dosis pakan 20-30%',
                'adjustment' => -25,
                'reason' => 'Udang cenderung malas makan saat hujan, kelebihan pakan bisa mencemari air'
            ];
        } elseif ($weatherMain === 'Clouds' || str_contains($cuaca, 'Berawan')) {
            return [
                'status' => 'info',
                'message' => 'Cuaca berawan, pakan normal',
                'adjustment' => 0,
                'reason' => 'Kondisi normal untuk pemberian pakan'
            ];
        } elseif ($weatherMain === 'Clear' || str_contains($cuaca, 'Cerah')) {
            return [
                'status' => 'success',
                'message' => 'Cuaca cerah, pakan normal hingga +5%',
                'adjustment' => 5,
                'reason' => 'Udang lebih aktif makan saat cuaca cerah'
            ];
        } else {
            return [
                'status' => 'info',
                'message' => 'Pakan normal sesuai jadwal',
                'adjustment' => 0,
                'reason' => 'Kondisi normal'
            ];
        }
    }

    /**
     * Get default weather when API fails
     */
    private function getDefaultWeather()
    {
        return [
            'success' => false,
            'location' => 'Desa Nambakor, Sumenep',
            'cuaca' => 'Cerah',
            'cuaca_en' => 'Clear',
            'intensitas_hujan' => 0,
            'suhu' => 28,
            'suhu_min' => 26,
            'suhu_max' => 31,
            'kelembaban' => 75,
            'tekanan' => 1010,
            'kecepatan_angin' => 5,
            'keterangan' => 'Menggunakan data default (koneksi API bermasalah)',
            'icon' => '01d',
            'feed_recommendation' => [
                'status' => 'info',
                'message' => 'Gunakan data manual, API tidak tersedia',
                'adjustment' => 0,
                'reason' => 'Data cuaca tidak dapat diperbarui'
            ],
            'last_update' => now()->toIso8601String(),
            'is_default' => true,
        ];
    }

    /**
     * Get 5-day weather forecast
     */
    public function getForecast($days = 5)
    {
        $cacheKey = "forecast_sumenep";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($days) {
            try {
                $response = Http::timeout(10)->get("https://api.openweathermap.org/data/2.5/forecast", [
                    'lat' => self::SUMENEP_LAT,
                    'lon' => self::SUMENEP_LON,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang' => 'id',
                    'cnt' => $days * 8 // 8 data per hari (3 jam sekali)
                ]);

                if ($response->successful()) {
                    return $this->formatForecastData($response->json());
                }

                return [];

            } catch (\Exception $e) {
                Log::error("Forecast API Error: " . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Format forecast data
     */
    private function formatForecastData($data)
    {
        $forecast = [];
        $list = $data['list'] ?? [];
        
        foreach ($list as $item) {
            $date = date('Y-m-d', $item['dt']);
            $dateObj = \Carbon\Carbon::parse($date);
            
            if (!isset($forecast[$date])) {
                $forecast[$date] = [
                    'date' => $date,
                    'day_name' => $dateObj->isoFormat('dddd'),
                    'temperatures' => [],
                    'weathers' => [],
                    'min_temp' => 999,
                    'max_temp' => -999,
                ];
            }
            
            $temp = $item['main']['temp'];
            $forecast[$date]['temperatures'][] = $temp;
            $forecast[$date]['min_temp'] = min($forecast[$date]['min_temp'], $temp);
            $forecast[$date]['max_temp'] = max($forecast[$date]['max_temp'], $temp);
            $forecast[$date]['weathers'][] = $item['weather'][0]['main'];
        }
        
        // Hitung rata-rata dan cuaca dominan
        foreach ($forecast as &$day) {
            $day['avg_temp'] = round(array_sum($day['temperatures']) / count($day['temperatures']), 1);
            $day['weather'] = $this->getDominantWeather($day['weathers']);
            $day['weather_id'] = $this->mapWeatherToIndonesian($day['weather'], 0);
            unset($day['temperatures'], $day['weathers']);
        }
        
        return array_values($forecast);
    }

    /**
     * Get dominant weather from array
     */
    private function getDominantWeather($weathers)
    {
        $counts = array_count_values($weathers);
        arsort($counts);
        return key($counts);
    }

    /**
     * Check if API key is valid
     */
    public function isApiKeyValid()
    {
        if (empty($this->apiKey) || $this->apiKey === 'your_api_key_here') {
            return false;
        }
        
        try {
            $response = Http::timeout(5)->get("https://api.openweathermap.org/data/2.5/weather", [
                'q' => 'Sumenep',
                'appid' => $this->apiKey,
                'units' => 'metric'
            ]);
            
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Clear weather cache
     */
    public function clearCache()
    {
        Cache::forget('weather_Sumenep');
        Cache::forget("weather_coord_{$this->defaultLat}_{$this->defaultLon}");
        Cache::forget('forecast_sumenep');
        
        return true;
    }
}