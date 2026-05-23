@extends('footbar.utama')

@section('title', 'Premium History Monitoring')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/history.css') }}">
@endpush

@section('content')
<div class="premium-history-page">
    <div class="premium-wrapper">
        
        <!-- HERO PREMIUM -->
        <div class="premium-hero">
            <div class="hero-left">
                <div class="hero-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="hero-text">
                    <h1><i class="fas fa-chart-line"></i> Premium History Monitoring</h1>
                    <p><i class="fas fa-chart-pie"></i> Advanced Analytics · <i class="fas fa-robot"></i> Smart Insights · AI Predictions</p>
                </div>
            </div>
            <div class="premium-badge">
                <i class="fas fa-gem"></i> PREMIUM MEMBER
            </div>
        </div>

        <!-- STATISTIK PREMIUM (4 Card - dengan Biomassa) -->
        <div class="stats-premium-grid">
            <div class="stat-premium-card">
                <div class="stat-icon">📊</div>
                <div class="stat-value" id="statPh">7.0</div>
                <div class="stat-label">Rata-rata pH</div>
            </div>
            <div class="stat-premium-card">
                <div class="stat-icon">💧</div>
                <div class="stat-value" id="statTurb">12 <span style="font-size:14px;">NTU</span></div>
                <div class="stat-label">Rata-rata Kekeruhan</div>
            </div>
            <div class="stat-premium-card">
            <div class="stat-icon">🍽️</div>
            <div class="stat-value" id="statFeed">0 <span style="font-size:14px;">kg</span></div>
            <div class="stat-label">Total Pakan</div>
            </div>
            <div class="stat-premium-card">
                <div class="stat-icon">🐟</div>
                <div class="stat-value" id="statBiomassa">0 <span style="font-size:14px;">kg</span></div>
                <div class="stat-label">Biomassa</div>
            </div>
        </div>

        <!-- TIMELINE BUDIDAYA -->
        <div class="timeline-premium">
            <div class="timeline-header">
                <div>
                    <h2><i class="fas fa-chart-line"></i> Timeline Budidaya</h2>
                    <p>Klik minggu untuk melihat data harian · <span id="currentWeekInfo">Minggu 1</span></p>
                </div>
            </div>
            <div class="timeline-track" id="timelineTrack">
                @foreach($weeksData as $week)
                <div class="week-card {{ !$week['has_data'] ? 'empty' : '' }}" 
                     data-week="{{ $week['week'] }}"
                     onclick="selectWeek({{ $week['week'] }})">
                    @php
                        $badgeClass = 'normal';
                        if (!$week['has_data']) $badgeClass = 'mendatang';
                        elseif ($week['status'] == 'Perhatian') $badgeClass = 'perhatian';
                        elseif ($week['status'] == 'Kritis') $badgeClass = 'kritis';
                    @endphp
                    <div class="week-badge {{ $badgeClass }}">{{ $week['status'] ?? 'Mendatang' }}</div>
                    <div class="week-title">{{ $week['period'] }}</div>
                    <div class="week-stats">
                        @if($week['has_data'])
                            <div><small>pH</small><br><strong id="weekPh-{{ $week['week'] }}">{{ $week['avg_ph'] ?? '-' }}</strong></div>
                            <div><small>Turb</small><br><strong id="weekTurb-{{ $week['week'] }}">{{ $week['avg_turbidity'] ?? '-' }}</strong></div>
                            <div><small>Pakan</small><br><strong id="weekFeed-{{ $week['week'] }}">{{ number_format($week['total_feed'] ?? 0, 1) }}kg</strong></div>
                        @else
                            <div><small>pH</small><br><span class="empty-value">--</span></div>
                            <div><small>Turb</small><br><span class="empty-value">--</span></div>
                            <div><small>Pakan</small><br><span class="empty-value">--</span></div>
                        @endif
                    </div>
                    <div style="margin-top: 10px; font-size: 11px; color: #64748b;">
                        <i class="fas fa-calendar-alt"></i> {{ $week['date_range'] ?? '' }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- DATA MONITORING HARIAN -->
        <div class="daily-card">
            <div class="card-header-daily">
                <h3><i class="fas fa-calendar-day"></i> Data Monitoring Harian - <span id="dailyWeekTitle">Minggu 1</span></h3>
            </div>
            <div style="overflow-x: auto;">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th><th>Hari</th><th>Rata-rata pH</th><th>Rata-rata Kekeruhan (NTU)</th><th>Total Pakan (kg)</th><th>Status</th><th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="dailyTableBody">
                        <tr><td colspan="7" class="text-center">Pilih minggu untuk melihat data</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- MODAL DETAIL -->
<div id="detailModal" class="modal-premium">
    <div class="modal-premium-content">
        <div class="modal-premium-header">
            <h3 id="modalTitle">Detail Monitoring</h3>
            <button class="modal-premium-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-premium-body" id="modalBody">
            <div class="text-center">Memuat data...</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Data dari server
    window.historyData = {
        weeksData: @json($weeksData),
        currentWeek: {{ $currentMinggu ?? 1 }},
        biomassaKg: {{ $biomassaKg ?? 0 }}
    };
</script>
<script src="{{ asset('js/history.js') }}"></script>
@endpush