@extends('footbar.utama')

@section('title', 'Dashboard Tambak Udang')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/home.css') }}">
<style>
    /* Memastikan teks di card cuaca berwarna hitam */
    .stat-card .stat-info h3,
    .stat-card .stat-info p,
    .stat-card .stat-info small {
        color: #000000 !important;
    }
    
    /* Alert box teks hitam */
    .alert-premium {
        color: #000000 !important;
    }
    
    /* Welcome header teks tetap putih */
    .welcome-header h2, 
    .welcome-header p {
        color: white !important;
    }
    
    /* Card lainnya tetap */
    .feeding-card, .info-list li span, .info-list li strong {
        color: #000000;
    }
    
    /* Gauge label */
    .gauge-label {
        color: #000000 !important;
    }
</style>
@endpush

@section('content')
<div class="dashboard-container">
    
    <!-- WELCOME HEADER dengan data dari Profile Tambak -->
    <div class="welcome-header" style="margin-bottom: 20px; background: rgba(255,255,255,0.15); padding: 15px 20px; border-radius: 16px; backdrop-filter: blur(8px);">
        <h2 style="font-size: 24px; font-weight: 600; color: white; text-shadow: 1px 1px 2px rgba(0,0,0,0.3); margin: 0;">
            Selamat Datang, {{ session('user_name') ?? 'Petambak' }}! 👋
        </h2>
        <p style="color: rgba(255,255,255,0.9); margin: 5px 0 0;">
            <i class="fas fa-fish"></i> {{ $profileData['nama_tambak'] ?? session('tambak_name') ?? 'Tambak Berkah' }} • 
            <i class="fas fa-map-marker-alt"></i> {{ $profileData['lokasi'] ?? session('lokasi_tambak') ?? 'Desa Nambakor, Sumenep' }}
        </p>
    </div>
    
    <!-- STATS GRID - 3 CARD (PAKAN, KONDISI AIR, POPULASI) -->
    <div class="stats-grid">
        <!-- CARD 1: PAKAN HARI INI -->
        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-info">
                <h3 id="topFeed">{{ $pakanHariIni ?? 0 }} <span>kg</span></h3>
                <p>Pakan Hari Ini</p>
            </div>
        </div>
        
        <!-- CARD 2: KONDISI AIR -->
        <div class="stat-card">
            <div class="stat-icon">💧</div>
            <div class="stat-info">
                <h3 id="topWater">Memuat...</h3>
                <p>Kondisi Air</p>
            </div>
        </div>
        
        <!-- CARD 3: POPULASI -->
        <div class="stat-card">
            <div class="stat-icon">🦐</div>
            <div class="stat-info">
                <h3>{{ number_format($populasi ?? 5000) }} <span>ekor</span></h3>
                <p>Populasi</p>
            </div>
        </div>
    </div>

    <!-- ==================== TAMBAHAN: CARD CUACA & UMUR ==================== -->
    <div class="stats-grid" style="margin-top: -15px;">
        <div class="stat-card">
            <div class="stat-icon">🌤️</div>
            <div class="stat-info">
                <h3 id="cuacaText">
                    @if(isset($weather) && $weather['success'])
                        {{ $weather['cuaca'] }}
                    @else
                        Memuat...
                    @endif
                </h3>
                <p id="suhuText">
                    @if(isset($weather) && $weather['success'])
                        Suhu: {{ $weather['suhu'] }}°C
                    @else
                        Suhu: --°C
                    @endif
                </p>
                <small style="font-size: 10px;" id="lokasiText">
                    @if(isset($weather) && $weather['success'])
                        📍 {{ $weather['location'] ?? 'Nambakor, Sumenep' }}
                    @endif
                </small>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">☔</div>
            <div class="stat-info">
                <h3 id="hujanText">
                    @if(isset($weather) && $weather['success'])
                        {{ $weather['intensitas_hujan'] }} <span>mm</span>
                    @else
                        0 <span>mm</span>
                    @endif
                </h3>
                <p>Intensitas Hujan</p>
                <small style="font-size: 10px;" id="hujanKeterangan">
                    @if(isset($weather) && $weather['success'] && $weather['intensitas_hujan'] > 0)
                        🌧️ Hujan dalam 1 jam terakhir
                    @elseif(isset($weather) && $weather['success'])
                        ☀️ Tidak ada hujan
                    @endif
                </small>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-info">
                <h3 id="umurText">{{ $umur_minggu ?? 0 }} <span>Minggu</span></h3>
                <p>Umur Budidaya</p>
            </div>
        </div>
    </div>
    <!-- ============================================================= -->

    <!-- ALERT BOX -->
    <div id="alertBox" class="alert-premium normal">
        @if(isset($weather) && $weather['success'] && isset($weather['feed_recommendation']))
            {!! $weather['feed_recommendation']['message'] !!}
        @else
            ✅ Memuat data sensor...
        @endif
    </div>

    <!-- CONTENT GRID - 2 CARD (GAUGE + FEED) -->
    <div class="content-grid-two">
        
        <!-- GAUGE CARD -->
        <div class="gauge-card">
            <div class="card-header">
                <i class="fas fa-chart-simple"></i>
                <h3>Kualitas Air Real-time</h3>
            </div>
            <div class="gauge-container">
                <div class="gauge-item">
                    <div class="gauge-canvas">
                        <canvas id="phGauge" width="180" height="180"></canvas>
                        <div class="gauge-value">
                            <span class="gauge-number" id="phText">0</span>
                            <span class="gauge-unit">pH</span>
                        </div>
                    </div>
                    <div class="gauge-label">pH Air</div>
                    <span class="status-badge" id="phStatus">Normal</span>
                </div>
                <div class="gauge-item">
                    <div class="gauge-canvas">
                        <canvas id="turbGauge" width="180" height="180"></canvas>
                        <div class="gauge-value">
                            <span class="gauge-number" id="turbText">0</span>
                            <span class="gauge-unit">NTU</span>
                        </div>
                    </div>
                    <div class="gauge-label">Kekeruhan</div>
                    <span class="status-badge" id="turbStatus">Normal</span>
                </div>
            </div>
        </div>

        <!-- FEED CARD (REKOMENDASI PAKAN) - ELEGAN -->
        <div class="feeding-card">
            <div class="card-header">
                <i class="fas fa-utensils"></i>
                <h3>Rekomendasi Pakan</h3>
            </div>
            <ul class="info-list">
                <li><span>Frekuensi</span><strong>3x sehari</strong></li>
                <li><span>Waktu</span><strong>Pagi | Siang | Sore</strong></li>
                <li><span>Berdasarkan</span><strong>Kondisi air & biomassa</strong></li>
                <li><span>Cuaca Saat Ini</span>
                    <strong id="cuacaFeedInfo">
                        @if(isset($weather) && $weather['success'])
                            {{ $weather['cuaca'] }}
                        @else
                            Memuat...
                        @endif
                    </strong>
                </li>
                @if(isset($weather) && $weather['success'] && isset($weather['feed_recommendation']))
                <li style="color: {{ $weather['feed_recommendation']['status'] === 'warning' ? '#f59e0b' : ($weather['feed_recommendation']['status'] === 'success' ? '#10b981' : '#000000') }}">
                    <span>Rekomendasi Cuaca</span>
                    <strong>{{ $weather['feed_recommendation']['message'] }}</strong>
                </li>
                @endif
            </ul>
            <div class="feed-box">
                <div class="feed-label">ESTIMASI PAKAN</div>
                <div class="feed-value" id="feedValue">0 kg</div>
                <div class="feed-note">per 1x pemberian</div>
            </div>
            <div class="btn-group">
                <button class="btn-primary" onclick="kirimPakan()"><i class="fas fa-paper-plane"></i> Kirim Pakan</button>
                <button class="btn-secondary" onclick="openEdit()"><i class="fas fa-pen"></i> Manual</button>
            </div>
        </div>

    </div>

    <!-- CHARTS GRID -->
    <div class="charts-grid">
        <div class="chart-card">
            <div class="card-header"><i class="fas fa-chart-line"></i><h3>Grafik pH (15 data terakhir)</h3></div>
            <canvas id="chartPh"></canvas>
        </div>
        <div class="chart-card">
            <div class="card-header"><i class="fas fa-chart-line"></i><h3>Grafik Kekeruhan (15 data terakhir)</h3></div>
            <canvas id="chartTurb"></canvas>
        </div>
        <div class="chart-card">
            <div class="card-header"><i class="fas fa-chart-bar"></i><h3>Grafik Pakan Mingguan</h3></div>
            <canvas id="chartFeed"></canvas>
        </div>
    </div>

    <!-- BOTTOM GRID -->
    <div class="bottom-grid">
        <div class="info-card">
            <div class="card-header"><i class="fas fa-cog"></i><h3>Rule Engine</h3></div>
            <div id="ruleDetail" style="font-size:13px; line-height:1.8;"></div>
        </div>
        <div class="info-card">
            <div class="card-header"><i class="fas fa-database"></i><h3>Data Tambahan</h3></div>
            <ul class="info-list">
                <li><span>Umur</span><strong id="umurMinggu">{{ $umur_minggu ?? 0 }} Minggu</strong></li>
                <li><span>Berat Rata-rata</span><strong id="beratRata">{{ $berat_rata ?? 0 }} gram</strong></li>
                <li><span>Biomassa</span><strong id="biomassa">{{ round(($biomassa ?? 0) / 1000, 2) }} kg</strong></li>
                <li><span>Populasi</span><strong>{{ number_format($populasi ?? 5000) }} ekor</strong></li>
                <li><span>Target Panen</span><strong id="targetPanen">{{ $target_panen_kg ?? 0 }} kg</strong></li>
                <li><span>Target Size</span><strong id="targetSize">{{ $target_size_gram ?? 0 }} gram</strong></li>
            </ul>
        </div>
    </div>

</div>

<!-- MODAL EDIT -->
<div id="editModal" class="modal-premium">
    <div class="modal-content-premium">
        <i class="fas fa-pen" style="font-size: 40px; color: #667eea;"></i>
        <h3>Kirim Pakan Manual</h3>
        <input type="number" id="manualPakan" placeholder="Masukkan pakan (gram)" step="1" min="1">
        <div class="btn-group">
            <button class="btn-primary" onclick="sendEdit()">Kirim</button>
            <button class="btn-secondary" onclick="closeEdit()">Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/home.js') }}"></script>
<script>
    window.ruleSensor = @json($rule ?? null);
    
    // Data cuaca dari server (Sumenep - Nambakor)
    window.weatherData = @json($weather ?? null);
    
    // Data user dari session untuk JavaScript
    window.userData = {
        id: {{ session('user_id') }},
        name: "{{ session('user_name') }}",
        tambakName: "{{ $profileData['nama_tambak'] ?? session('tambak_name') ?? 'Tambak Berkah' }}",
        lokasi: "{{ $profileData['lokasi'] ?? session('lokasi_tambak') ?? 'Desa Nambakor, Sumenep' }}"
    };
    
    document.addEventListener('DOMContentLoaded', function() {
        // Tampilkan rule engine
        if (window.ruleSensor) {
            document.getElementById('ruleDetail').innerHTML = `
                <strong>📐 Detail Rule:</strong><br>
                ✅ pH Baik: ${window.ruleSensor.ph_min_good} - ${window.ruleSensor.ph_max_good}<br>
                ⚠️ pH Peringatan: ${window.ruleSensor.ph_min_warning} - ${window.ruleSensor.ph_max_warning}<br>
                ❌ pH Bahaya: < ${window.ruleSensor.ph_danger_low} atau > ${window.ruleSensor.ph_danger_high}<br>
                ✅ NTU Baik: ${window.ruleSensor.turbidity_min_good} - ${window.ruleSensor.turbidity_max_good}<br>
                ⚠️ NTU Peringatan: ${window.ruleSensor.turbidity_min_warning} - ${window.ruleSensor.turbidity_max_warning}<br>
                ❌ NTU Bahaya: < ${window.ruleSensor.turbidity_danger_low} atau > ${window.ruleSensor.turbidity_danger_high}
            `;
        }
        
        // Tampilkan data cuaca awal dari server
        if (window.weatherData && window.weatherData.success) {
            // Update tampilan cuaca
            if (document.getElementById('cuacaText')) {
                document.getElementById('cuacaText').innerHTML = window.weatherData.cuaca;
            }
            if (document.getElementById('suhuText')) {
                document.getElementById('suhuText').innerHTML = `Suhu: ${window.weatherData.suhu}°C`;
            }
            if (document.getElementById('hujanText')) {
                const hujan = window.weatherData.intensitas_hujan || 0;
                document.getElementById('hujanText').innerHTML = `${hujan} <span>mm</span>`;
            }
            if (document.getElementById('cuacaFeedInfo')) {
                document.getElementById('cuacaFeedInfo').innerHTML = window.weatherData.cuaca;
            }
            
            // Update alert berdasarkan cuaca
            if (window.weatherData.feed_recommendation) {
                const alertBox = document.getElementById('alertBox');
                const rec = window.weatherData.feed_recommendation;
                
                if (rec.status === 'warning') {
                    alertBox.className = 'alert-premium warning';
                    alertBox.innerHTML = `⚠️ ${rec.message}`;
                } else if (rec.status === 'success') {
                    alertBox.className = 'alert-premium success';
                    alertBox.innerHTML = `✅ ${rec.message}`;
                }
            }
        }
        
        // Inisialisasi semua fungsi
        if (typeof initAllCharts === 'function') setTimeout(initAllCharts, 100);
        if (typeof loadRealtime === 'function') { loadRealtime(); setInterval(loadRealtime, 3000); }
        if (typeof loadCuaca === 'function') { loadCuaca(); setInterval(loadCuaca, 1800000); }
        if (typeof loadProductionData === 'function') { loadProductionData(); setInterval(loadProductionData, 30000); }
    });
</script>
@endpush