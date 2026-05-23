<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FeedingRecord extends Model
{
    use HasFactory;

    protected $table = 'feeding_records';

    protected $fillable = [
        'pakan_kg',
        'target_gram',
        'jadwal',
        'waktu_pemberian',
        'status',
        'keterangan',
        'ph_saat_pemberian',
        'turbidity_saat_pemberian',
        'status_air_saat_pemberian',
        'tambak_profile_id',
        'cycle_id',
        'week',
        'date',
        'given_by'
    ];

    protected $casts = [
        'waktu_pemberian' => 'datetime:H:i',
        'ph_saat_pemberian' => 'decimal:2',
        'turbidity_saat_pemberian' => 'decimal:2',
        'target_gram' => 'decimal:2',
        'pakan_kg' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'date' => 'date'
    ];

    // ========== SCOPES ==========
    
    public function scopeHariIni($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    public function scopePadaTanggal($query, $date)
    {
        return $query->whereDate('created_at', $date);
    }

    public function scopeMingguIni($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeBulanIni($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    public function scopeRentangTanggal($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByJadwal($query, $jadwal)
    {
        return $query->where('jadwal', $jadwal);
    }
    
    public function scopeWeek($query, $week)
    {
        return $query->where('week', $week);
    }
    
    public function scopeByCycle($query, $cycleId)
    {
        return $query->where('cycle_id', $cycleId);
    }

    // ========== ACCESSORS ==========
    
    public function getWaktuDisplayAttribute()
    {
        return $this->waktu_pemberian ? date('H:i', strtotime($this->waktu_pemberian)) : '-';
    }

    public function getPakanGramFormattedAttribute()
    {
        return number_format($this->target_gram, 0, ',', '.') . ' gram';
    }

    public function getPakanKgFormattedAttribute()
    {
        return number_format($this->pakan_kg, 2, ',', '.') . ' kg';
    }

    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'Sudah', 'selesai', 'Selesai' => 'badge-success',
            'Belum', 'pending', 'Pending' => 'badge-warning',
            'Terlewat', 'missed', 'Missed' => 'badge-danger',
            default => 'badge-secondary'
        };
    }

    public function getStatusTextAttribute()
    {
        return match ($this->status) {
            'Sudah', 'selesai', 'Selesai' => '✓ Sudah',
            'Belum', 'pending', 'Pending' => '⌛ Belum',
            'Terlewat', 'missed', 'Missed' => '⚠️ Terlewat',
            default => $this->status
        };
    }
    
    public function getStatusBadgeHtmlAttribute()
    {
        $class = $this->status_badge_class;
        $text = $this->status_text;
        return "<span class='status-badge $class'>$text</span>";
    }

    // ========== STATIC METHODS ==========
    
    public static function totalHariIni()
    {
        // 🔥 PAKAI target_gram (dalam gram)
        return self::whereDate('created_at', Carbon::today())->sum('target_gram');
    }

    public static function totalKgHariIni()
    {
        return self::whereDate('created_at', Carbon::today())->sum('pakan_kg');
    }

    public static function totalMingguIni()
    {
        return self::mingguIni()->sum('target_gram');
    }

    public static function totalBulanIni()
    {
        return self::bulanIni()->sum('target_gram');
    }

    public static function rataRataHarianMingguIni()
    {
        $total = self::totalMingguIni();
        return $total > 0 ? round($total / 7, 0) : 0;
    }
    
    public static function totalPerWeek($cycleId, $week)
    {
        return self::where('cycle_id', $cycleId)
                    ->where('week', $week)
                    ->sum('target_gram');
    }
    
    public static function totalPerDay($cycleId, $date)
    {
        return self::where('cycle_id', $cycleId)
                    ->whereDate('date', $date)
                    ->sum('target_gram');
    }

    // ========== JADWAL PAKAN DEFAULT ==========
    
    public static function getScheduleWithStatus($date = null)
    {
        $targetDate = $date ? Carbon::parse($date) : Carbon::today();
        
        $existingFeedings = self::whereDate('created_at', $targetDate)
                        ->orderBy('created_at', 'asc')
                        ->get();
        
        if ($existingFeedings->isEmpty()) {
            return [];
        }
        
        $schedule = [];
        foreach ($existingFeedings as $feeding) {
            $schedule[] = [
                'pukul' => $feeding->created_at->format('H:i'),
                'waktu' => self::getWaktuName($feeding->created_at->hour),
                'icon' => self::getIconByHour($feeding->created_at->hour),
                'jumlah' => (float)$feeding->target_gram,  // 🔥 PAKAI target_gram
                'status' => 'Sudah',
                'record_id' => $feeding->id,
                'keterangan' => $feeding->keterangan
            ];
        }
        
        return $schedule;
    }
    
    public static function getHistoryByWeek($cycleId, $week)
    {
        return self::where('cycle_id', $cycleId)
                    ->where('week', $week)
                    ->orderBy('date')
                    ->orderBy('waktu_pemberian')
                    ->get()
                    ->map(function($item) {
                        return [
                            'id' => $item->id,
                            'date' => $item->date ?? $item->created_at->format('Y-m-d'),
                            'time' => $item->waktu_pemberian ? date('H:i', strtotime($item->waktu_pemberian)) : $item->created_at->format('H:i'),
                            'amount' => $item->target_gram,  // 🔥 PAKAI target_gram
                            'amount_kg' => $item->pakan_kg,
                            'note' => $item->keterangan,
                            'status' => $item->status,
                            'status_text' => $item->status_text
                        ];
                    });
    }

    private static function getWaktuName($hour)
    {
        if ($hour >= 5 && $hour < 9) return 'Pagi';
        if ($hour >= 9 && $hour < 13) return 'Siang';
        if ($hour >= 13 && $hour < 17) return 'Sore';
        return 'Malam';
    }

    private static function getIconByHour($hour)
    {
        if ($hour >= 5 && $hour < 9) return '🌅';
        if ($hour >= 9 && $hour < 13) return '☀️';
        if ($hour >= 13 && $hour < 17) return '🌤️';
        return '🌙';
    }

    public static function deleteByDate($date)
    {
        return self::whereDate('created_at', $date)->delete();
    }
    
    public static function getFeedRecommendation($currentPh, $currentTurbidity, $normalFeed = 500)
    {
        $reductionPercent = 0;
        $reason = '';
        
        if ($currentTurbidity > 60) {
            $reductionPercent = 50;
            $reason = 'Air sangat keruh, pakan dikurangi 50%';
        } elseif ($currentTurbidity > 30) {
            $reductionPercent = 30;
            $reason = 'Air keruh, pakan dikurangi 30%';
        } elseif ($currentPh < 6.5 || $currentPh > 8.5) {
            $reductionPercent = 20;
            $reason = 'pH tidak normal, pakan dikurangi 20%';
        } else {
            $reason = 'Kondisi air normal, pakan sesuai target';
        }
        
        $recommendedFeed = $normalFeed - ($normalFeed * $reductionPercent / 100);
        
        return [
            'normal_feed' => $normalFeed,
            'recommended_feed' => round($recommendedFeed),
            'reduction_percent' => $reductionPercent,
            'reason' => $reason,
            'status' => $reductionPercent > 0 ? 'warning' : 'normal'
        ];
    }

    /**
     * GET FEEDING STATS UNTUK POSTGRESQL (pakai target_gram)
     */
    public static function getFeedingStats()
    {
        $hariIni = self::totalHariIni();
        $mingguIni = self::totalMingguIni();
        $bulanIni = self::totalBulanIni();
        
        // 🔥 QUERY MANUAL UNTUK POSTGRESQL (pakai target_gram)
        $perJam = [];
        $results = DB::select("
            SELECT EXTRACT(HOUR FROM created_at) as jam, SUM(target_gram) as total
            FROM feeding_records
            WHERE DATE(created_at) = ?
            GROUP BY EXTRACT(HOUR FROM created_at)
            ORDER BY jam ASC
        ", [Carbon::today()->toDateString()]);
        
        foreach ($results as $row) {
            $perJam[(int)$row->jam] = (float)$row->total;
        }
        
        $chartData = [];
        for ($i = 6; $i <= 22; $i++) {
            $chartData[$i] = $perJam[$i] ?? 0;
        }
        
        $schedule = self::getScheduleWithStatus();
        
        $completedCount = collect($schedule)->filter(fn($s) => $s['status'] == 'Sudah')->count();
        $completedTotal = collect($schedule)->filter(fn($s) => $s['status'] == 'Sudah')->sum('jumlah');
        $remainingTotal = collect($schedule)->filter(fn($s) => $s['status'] == 'Belum')->sum('target_gram');
        $predictedTotal = $completedTotal + $remainingTotal;
        
        return [
            'hari_ini' => [
                'gram' => $hariIni,
                'kg' => round($hariIni / 1000, 2),
                'jadwal' => $schedule,
                'selesai' => $completedCount,
                'total_jadwal' => count($schedule),
                'predicted_total' => $predictedTotal
            ],
            'minggu_ini' => [
                'gram' => $mingguIni,
                'kg' => round($mingguIni / 1000, 2),
                'rata_rata_harian' => self::rataRataHarianMingguIni()
            ],
            'bulan_ini' => [
                'gram' => $bulanIni,
                'kg' => round($bulanIni / 1000, 2)
            ],
            'chart_data' => $chartData
        ];
    }

    public static function recordFeeding($gram, $pukul, $additional = [])
    {
        $carbonTime = Carbon::today()->setHour(substr($pukul, 0, 2))->setMinute(substr($pukul, 3, 2));
        
        $data = array_merge([
            'target_gram' => $gram,           // 🔥 simpan di target_gram (gram)
            'pakan_kg' => $gram / 1000,       // 🔥 simpan di pakan_kg (kg)
            'jadwal' => $additional['jadwal'] ?? self::getJadwalName($pukul),
            'waktu_pemberian' => $carbonTime,
            'status' => 'Sudah',
            'keterangan' => $additional['keterangan'] ?? null,
            'ph_saat_pemberian' => $additional['ph'] ?? null,
            'turbidity_saat_pemberian' => $additional['turbidity'] ?? null,
            'status_air_saat_pemberian' => $additional['status_air'] ?? null,
            'tambak_profile_id' => $additional['tambak_profile_id'] ?? null
        ], $additional);
        
        $existing = self::whereDate('created_at', Carbon::today())
                        ->whereTime('created_at', '>=', $carbonTime->copy()->subMinutes(30))
                        ->whereTime('created_at', '<=', $carbonTime->copy()->addMinutes(30))
                        ->first();
        
        if ($existing) {
            $existing->update($data);
            return $existing;
        }
        
        return self::create($data);
    }
    
    private static function getJadwalName($pukul)
    {
        $hour = (int)substr($pukul, 0, 2);
        
        if ($hour >= 5 && $hour < 9) return 'Pagi';
        if ($hour >= 9 && $hour < 13) return 'Siang';
        if ($hour >= 13 && $hour < 17) return 'Sore';
        return 'Malam';
    }

    // ========== RELATIONSHIPS ==========
    
    public function tambakProfile()
    {
        return $this->belongsTo(TambakProfile::class, 'tambak_profile_id');
    }
    
    public function cycle()
    {
        return $this->belongsTo(Cycle::class, 'cycle_id');
    }
}