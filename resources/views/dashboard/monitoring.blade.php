@extends('footbar.utama')

@section('title', 'Detail Monitoring Tambak')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/monitoring.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush

@section('content')
<div class="monitoring-container">

    <!-- HERO SECTION -->
    <div class="monitoring-hero">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="hero-icon">
                <i class="fas fa-chart-line fa-fw"></i>
            </div>
            <div class="hero-text">
                <h1><i class="fas fa-tachometer-alt"></i> Detail Monitoring Tambak</h1>
                <p><i class="fas fa-map-marker-alt"></i> Pond A • <i class="fas fa-chart-line"></i> Real-time • <i class="fas fa-sync-alt"></i> Auto Update</p>
            </div>
        </div>
        <div class="hero-stats">
            <div class="hero-stat"><i class="fas fa-calendar"></i><span id="heroDate"></span></div>
            <div class="hero-stat"><i class="fas fa-clock"></i><span id="heroTime"></span></div>
        </div>
    </div>

    <!-- STATS CARD (4 Card) -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-info">
                <h3 id="phStatusDisplay" class="status-text">{{ $phStatus ?? 'Normal' }}</h3>
                <p>Terpantau pH</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">💧</div>
            <div class="stat-info">
                <h3 id="turbidityStatusDisplay" class="status-text">{{ $turbidityStatus ?? 'Normal' }}</h3>
                <p>Terpantau Turbidity</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">🍽️</div>
            <div class="stat-info">
                <h3 id="totalFeedDisplay">{{ $totalFeed ?? '0' }} <span>gram</span></h3>
                <p>Total Pakan Hari Ini</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">🐟</div>
            <div class="stat-info">
                <h3 id="biomassaDisplay">{{ number_format($biomassaKg ?? 0, 2) }} <span>kg</span></h3>
                <p>Biomassa</p>
            </div>
        </div>
    </div>

    <!-- CONTENT GRID - 2 KOLOM -->
    <div class="content-grid-two">
        
        <!-- KIRI: Grafik Monitoring -->
        <div class="info-card-elegant">
            <div class="card-header-elegant">
                <div class="header-icon"><i class="fas fa-chart-line"></i></div>
                <div><h3>Grafik Monitoring</h3><p>pH & Kekeruhan Air</p></div>
            </div>
            <div class="card-body-elegant">
                <div class="gauges-wrapper">
                    <div class="gauge-item">
                        <div class="gauge-container">
                            <canvas id="phGauge" width="180" height="180"></canvas>
                            <div class="gauge-center-value" id="phCenterVal">{{ $currentPh ?? '7.8' }}</div>
                        </div>
                        <div class="gauge-info">
                            <h4><i class="fas fa-droplet"></i> pH Air</h4>
                            <span class="gauge-badge" id="phGaugeBadge">{{ $currentStatus ?? 'Normal' }}</span>
                        </div>
                    </div>

                    <div class="gauge-item">
                        <div class="gauge-container">
                            <canvas id="turbGauge" width="180" height="180"></canvas>
                            <div class="gauge-center-value" id="turbCenterVal">{{ $currentTurbidity ?? '30' }}</div>
                        </div>
                        <div class="gauge-info">
                            <h4><i class="fas fa-cloud-rain"></i> Kekeruhan</h4>
                            <span class="gauge-badge" id="turbGaugeBadge">
                                @php
                                    $turb = $currentTurbidity ?? 30;
                                    if($turb < 30) echo 'Normal';
                                    elseif($turb < 60) echo 'Sedang';
                                    else echo 'Tinggi';
                                @endphp
                            </span>
                            <span class="unit-text">NTU</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer-elegant">
                <button class="refresh-btn-full" id="refreshDataBtn"><i class="fas fa-sync-alt"></i> Refresh Data</button>
            </div>
        </div>

        <!-- KANAN: Rekomendasi Pakan -->
        <div class="photo-card-elegant">
            <div class="card-header-elegant">
                <div class="header-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div><h3>Rekomendasi Pakan</h3><p>Berdasarkan kondisi air terkini</p></div>
            </div>
            <div class="recommendation-body">
                <div class="alert-warning-box" id="alertWarningBox">
                    <i class="fas fa-tint"></i>
                    <span id="alertReason">{{ $rekomendasi['reason'] ?? 'Kondisi air normal, pakan sesuai target' }}</span>
                </div>
                <div class="feed-stats">
                    <div class="feed-stat-item">
                        <span class="feed-label">Jumlah Pakan Hari Ini</span>
                        <span class="feed-number" id="recommendFeedValue">{{ $totalFeed ?? '0' }}</span>
                        <span class="feed-unit">gram</span>
                    </div>
                    <div class="feed-stat-item">
                        <span class="feed-label">Jumlah Pemberian Hari Ini</span>
                        <span class="feed-number" id="jumlahPemberianDisplay">{{ $jumlahPemberianHariIni ?? '0' }}</span>
                        <span class="feed-unit">x pemberian</span>
                    </div>
                </div>
                <div class="progress-container">
                    <div class="progress-label"><span>📊 Target Pakan Normal</span><span id="normalFeedTarget">500 gram</span></div>
                    <div class="progress-bar"><div class="progress-fill" id="progressFill" style="width: {{ $totalFeed ? min(100, ($totalFeed / 500) * 100) : 0 }}%"></div></div>
                    <div class="progress-note" id="progressNote">Realisasi: {{ $totalFeed ?? '0' }} gram ({{ $totalFeed ? round(($totalFeed / 500) * 100) : 0 }}%)</div>
                </div>
                <div class="shrimp-animation">
                    <i class="fas fa-shrimp"></i><i class="fas fa-shrimp"></i><i class="fas fa-shrimp"></i><i class="fas fa-shrimp"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- BOTTOM GRID: Tabel Data -->
    <div class="bottom-grid">
        <!-- TABEL DATA MONITORING -->
        <div class="quality-card">
            <div class="card-header">
                <span class="card-icon">📊</span>
                <h3>Data Monitoring (per 5 menit)</h3>
                <div class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchMonitoring" placeholder="Cari jam... (contoh: 06:00, 12:00)">
                </div>
            </div>
            <div class="date-picker-wrapper">
                <i class="fas fa-calendar-alt"></i>
                <input type="date" id="monitoringDate" value="{{ date('Y-m-d') }}" class="date-input">
            </div>
            <div class="table-responsive">
                <table class="monitor-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;"><i class="far fa-clock"></i> Waktu</th>
                            <th style="width: 80px;"><i class="fas fa-flask"></i> pH</th>
                            <th><i class="fas fa-cloud-rain"></i> Kekeruhan (NTU)</th>
                            <th style="width: 100px;"><i class="fas fa-chart-simple"></i> Status</th>
                        </tr>
                    </thead>
                    <tbody id="monitoringTableBody">
                        <tr><td colspan="4" style="text-align:center;">Memuat数据...</div></div>
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted" id="monitoringInfo">
                        <i class="fas fa-info-circle"></i> Memuat data...
                    </small>
                    <small class="text-muted" id="lastUpdateInfo">
                        <i class="fas fa-clock"></i> Update real-time setiap 30 detik
                    </small>
                </div>
            </div>
        </div>

        <!-- TABEL DETAIL PAKAN -->
        <div class="budidaya-card">
            <div class="card-header">
                <span class="card-icon">🍽️</span>
                <h3>Detail Pakan</h3>
                <div class="recomend-badge">
                    <i class="fas fa-check-circle"></i> Data Real Manual
                </div>
            </div>
            <div class="table-responsive">
                <table class="monitor-table">
                    <thead>
                        <tr>
                            <th style="width: 40%;"><i class="far fa-clock"></i> Waktu</th>
                            <th style="width: 30%;"><i class="fas fa-weight-hanging"></i> Jumlah</th>
                            <th style="width: 30%;"><i class="fas fa-circle-check"></i> Status</th>
                        </tr>
                    </thead>
                    <tbody id="feedTableBody">
                        @forelse($feedSchedule ?? [] as $feed)
                        <tr>
                            <td>
                                {{ $feed['icon'] ?? '🍽️' }} {{ $feed['pukul'] ?? '-' }}
                                @if(isset($feed['waktu']))
                                    <small style="color:#94a3b8;">({{ $feed['waktu'] }})</small>
                                @endif
                                @if(isset($feed['keterangan']))
                                    <br><small style="color:#94a3b8; font-size:11px;">{{ $feed['keterangan'] }}</small>
                                @endif
                            </div>
                            <td><strong>{{ $feed['jumlah'] ?? 0 }}</strong> <span style="color:#94a3b8;">gram</span></div>
                            <td>
                                <span class="status-badge {{ ($feed['status'] ?? 'Belum') == 'Sudah' ? 'success' : 'warning' }}">
                                    {{ ($feed['status'] ?? 'Belum') == 'Sudah' ? '✓ Sudah' : '⌛ Belum' }}
                                </span>
                            </div>
                        </tr>
                        @empty
                        <tr><td colspan="3" style="text-align:center; padding: 30px;">📭 Belum ada catatan pemberian pakan hari ini</div></td>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> Data pakan yang sudah dicatat manual
                </small>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/monitoring.js') }}"></script>
@endpush