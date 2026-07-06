{{-- resources/views/dashboard/profile.blade.php --}}
@extends('footbar.utama')

@section('title', 'Profil Tambak')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">

@php
    use Carbon\Carbon;

    $start = ($profile && $profile->tanggal_mulai_budidaya)
        ? Carbon::parse($profile->tanggal_mulai_budidaya)
        : null;

    $today = Carbon::now();
    $days = $start ? max(0, (int) $start->diffInDays($today)) : 0;
    $total = 90;
    $percent = $start ? min(($days / $total) * 100, 100) : 0;
    $estimasiPanen = $start ? $start->copy()->addDays(90)->format('d M Y') : '-';
    
    $populasi = $profile->populasi ?? 5000;
    $avgWeight = $profile->avg_weight ?? 15;
    $biomassaKg = ($populasi * $avgWeight) / 1000;
    
    $umurMinggu = $start ? max(1, ceil($days / 7)) : 1;
    $tabelPakan = [1=>0.5,2=>1.0,3=>2.0,4=>3.0,5=>4.5,6=>6.0,7=>8.0,8=>10.0,9=>12.5,10=>15.0,11=>18.0,12=>21.0,13=>25.0];
    $pakanPerEkor = $tabelPakan[$umurMinggu] ?? (25.0 + (($umurMinggu - 13) * 3.5));
    $pakanPerHariGram = $populasi * $pakanPerEkor;
    $pakanPerHariKg = round($pakanPerHariGram / 1000, 2);
    
    use App\Models\FeedingRecord;
    $pakanHariIniKg = FeedingRecord::whereDate('created_at', today())->sum('pakan_kg');
@endphp

<div class="profile-container">

    <!-- HERO SECTION -->
    <div class="profile-hero">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="hero-icon">
                <img src="{{ asset('images/pengaturan.png') }}" alt="Profile Icon">
            </div>
            <div class="hero-text">
                <h1>Profil Tambak</h1>
                <p>Kelola informasi dan pantau perkembangan budidaya udang Anda</p>
            </div>
        </div>
    </div>

    <!-- STATS CARD -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">🦐</div>
            <div class="stat-info">
                <h3>{{ number_format($populasi) }}</h3>
                <p>Populasi Udang</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⚖️</div>
            <div class="stat-info">
                <h3>{{ number_format($avgWeight, 2) }} <span>gram</span></h3>
                <p>Berat Rata-rata</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-info">
                <h3>{{ number_format($biomassaKg, 2) }} <span>kg</span></h3>
                <p>Biomassa Total</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🍽️</div>
            <div class="stat-info">
                <h3 id="pakanHariIniProfile">{{ number_format($pakanHariIniKg, 2) }} <span>kg</span></h3>
                <p>Pakan Hari Ini</p>
            </div>
        </div>
    </div>

    <!-- CONTENT GRID - 2 KOLOM -->
    <div class="content-grid-two">
        
        <!-- KIRI: Informasi Tambak -->
        <div class="info-card-elegant">
            <div class="card-header-elegant">
                <div class="header-icon">
                    <i class="fas fa-water"></i>
                </div>
                <div>
                    <h3>Informasi Tambak</h3>
                    <p>Data detail lokasi dan spesifikasi tambak</p>
                </div>
            </div>
            <div class="card-body-elegant">
                <div class="info-row-elegant">
                    <div class="info-label">
                        <i class="fas fa-tag"></i>
                        <span>Nama Tambak</span>
                    </div>
                    <div class="info-value">{{ $profile->nama_tambak ?? '-' }}</div>
                </div>
                <div class="info-row-elegant">
                    <div class="info-label">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Lokasi</span>
                    </div>
                    <div class="info-value">{{ $profile->lokasi ?? '-' }}</div>
                </div>
                <div class="info-row-elegant">
                    <div class="info-label">
                        <i class="fas fa-expand-alt"></i>
                        <span>Luas Tambak</span>
                    </div>
                    <div class="info-value">{{ number_format($profile->luas ?? 0, 0) }} m²</div>
                </div>
                <div class="info-row-elegant">
                    <div class="info-label">
                        <i class="fas fa-tint"></i>
                        <span>Tipe Tambak</span>
                    </div>
                    <div class="info-value">{{ $profile->tipe_tambak ?? '-' }}</div>
                </div>
                <div class="info-row-elegant">
                    <div class="info-label">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Tanggal Dibuat</span>
                    </div>
                    <div class="info-value">{{ $profile->tanggal_dibuat ?? '-' }}</div>
                </div>
            </div>
            <div class="card-footer-elegant">
                <button class="edit-btn-full" onclick="openEditModal()">
                    <i class="fas fa-pen"></i> Edit Profil Tambak
                </button>
            </div>
        </div>

        <!-- KANAN: Foto Tambak -->
        <div class="photo-card-elegant">
            <div class="card-header-elegant">
                <div class="header-icon">
                    <i class="fas fa-camera"></i>
                </div>
                <div>
                    <h3>Foto Tambak</h3>
                    <p>Dokumentasi visual kondisi tambak</p>
                </div>
            </div>
            <div class="photo-container-elegant">
                <img src="{{ $profile && $profile->foto_tambak ? asset('storage/' . $profile->foto_tambak) : asset('images/tambak4.jpeg') }}" alt="Tambak">
                <div class="photo-overlay">
                    <span class="photo-badge">
                        <i class="fas fa-image"></i> Tambak
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- BOTTOM GRID -->
    <div class="bottom-grid">

        <!-- KUALITAS AIR CARD -->
        <div class="quality-card">
            <div class="card-header">
                <span class="card-icon">💧</span>
                <h3>Kualitas Air</h3>
                <span class="live-badge">LIVE</span>
            </div>
            <div class="quality-items">
                <div class="quality-item">
                    <div class="quality-icon">💧</div>
                    <div class="quality-info">
                        <span class="quality-label">pH Air</span>
                        <span class="quality-value" id="qualityPh">--</span>
                        <span class="quality-status" id="qualityPhStatus">--</span>
                        <small class="calibration-info" id="phCalibInfo"></small>
                    </div>
                </div>
                <div class="quality-item">
                    <div class="quality-icon">⚪</div>
                    <div class="quality-info">
                        <span class="quality-label">Turbidity</span>
                        <span class="quality-value" id="qualityTurb">--</span>
                        <span class="quality-status" id="qualityTurbStatus">--</span>
                        <small class="calibration-info" id="turbCalibInfo"></small>
                    </div>
                </div>
            </div>
            
            <div class="param-buttons">
                <button onclick="openKalibrasiModal()" class="param-btn">
                    <i class="fas fa-microscope"></i> Kalibrasi pH & Turbidity
                </button>
                <button onclick="resetAllCalibrationConfirm()" class="param-btn reset-btn">
                    <i class="fas fa-sync-alt"></i> Reset Kalibrasi
                </button>
            </div>
            
            <div id="calibrationStatus" class="calibration-status"></div>
        </div>

        <!-- MASA BUDIDAYA CARD -->
        <div class="budidaya-card">
            <div class="card-header">
                <span class="card-icon">📅</span>
                <h3>Masa Budidaya</h3>
            </div>
            @if(!$profile || !$start)
                <button class="start-btn" onclick="openBudidayaModal()">
                    <i class="fas fa-play"></i> Mulai Budidaya
                </button>
            @else
                <div class="budidaya-content">
                    <div class="budidaya-info">
                        <div class="budidaya-age">
                            <span class="age-number">{{ $days }}</span>
                            <span class="age-label">Hari</span>
                        </div>
                        <div class="budidaya-detail">
                            <p>📊 Umur: <strong>{{ $umurMinggu }} Minggu</strong></p>
                            <p>📅 Mulai: <strong>{{ $profile->tanggal_mulai_budidaya }}</strong></p>
                            <p>🎯 Panen: <strong>{{ $estimasiPanen }}</strong></p>
                        </div>
                    </div>
                    <div class="progress-container">
                        <div class="progress-label">
                            <span>📈 Progress Budidaya</span>
                            <span>{{ round($percent) }}%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                    <div class="budidaya-actions">
                        <button class="edit-small-btn" onclick="openBudidayaModal()">
                            <i class="fas fa-pen"></i> Edit Tanggal
                        </button>
                        <button class="reset-small-btn" onclick="resetBudidaya()">
                            <i class="fas fa-sync-alt"></i> Reset
                        </button>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>

<!-- ==================== MODAL EDIT PROFILE ==================== -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>✏️ Edit Profil Tambak</h3>
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="text" name="nama_tambak" placeholder="Nama Tambak" value="{{ $profile->nama_tambak ?? '' }}">
            <input type="text" name="lokasi" placeholder="Lokasi" value="{{ $profile->lokasi ?? '' }}">
            <input type="number" step="0.01" name="luas" placeholder="Luas Tambak (m²)" value="{{ $profile->luas ?? '' }}">
            <input type="text" name="tipe_tambak" placeholder="Tipe Tambak" value="{{ $profile->tipe_tambak ?? '' }}">
            <input type="number" name="populasi" placeholder="Populasi Udang (ekor)" value="{{ $populasi }}">
            <input type="number" step="0.01" name="avg_weight" placeholder="Berat Rata-rata (gram/ekor)" value="{{ $avgWeight }}">
            <input type="date" name="tanggal_dibuat" value="{{ $profile->tanggal_dibuat ?? '' }}">
            <label>Foto Tambak</label>
            <input type="file" name="foto_tambak" accept="image/*" id="fotoInput">
            <img id="previewFoto" src="#" style="display:none; width:100%; margin-top:10px; border-radius:12px;">
            <div class="modal-action">
                <button type="button" onclick="closeEditModal()">Batal</button>
                <button type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL BUDIDAYA ==================== -->
<div id="budidayaModal" class="modal">
    <div class="modal-content">
        <h3>📅 Mulai / Edit Budidaya</h3>
        <form action="{{ route('budidaya.start') }}" method="POST">
            @csrf
            @method('PUT')
            <label>Tanggal Mulai Budidaya</label>
            <input type="date" name="tanggal_mulai_budidaya" value="{{ $profile->tanggal_mulai_budidaya ?? '' }}" required>
            <div class="modal-action">
                <button type="button" onclick="closeBudidayaModal()">Batal</button>
                <button type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL KALIBRASI OFFSET MANUAL ==================== -->
<div id="kalibrasiModal" class="modal" style="display:none;">
    <div class="modal-content" style="max-width: 500px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin:0;">🔧 Kalibrasi Offset Manual</h3>
            <button onclick="closeKalibrasiModal()" style="background:none; border:none; font-size:28px; cursor:pointer; color:#666;">&times;</button>
        </div>
        
        <div class="kalibrasi-info-box">
            <strong>💡 Cara Kalibrasi Mudah:</strong>
            <ol>
                <li>Ambil sampel air tambak (1 botol)</li>
                <li>Ukur dengan alat standar di rumah (pH meter/Turbidity meter)</li>
                <li>Hitung selisih: <strong>Offset = Nilai Standar - Nilai Sensor</strong></li>
                <li>Masukkan offset di bawah ini</li>
            </ol>
        </div>
        
        <div class="sensor-values-box">
            <p>📊 Nilai Sensor Saat Ini:</p>
            <p style="margin:0;">pH: <strong id="modalCurrentPh">--</strong> | Turbidity: <strong id="modalCurrentTurb">--</strong> NTU</p>
        </div>
        
        <!-- Kalibrasi pH -->
        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:8px; font-weight:600;">📈 Kalibrasi pH</label>
            <div class="offset-input-group">
                <input type="number" step="0.01" id="offsetPhInput" placeholder="Contoh: +0.30 atau -0.15">
                <button onclick="savePHOffset()">Simpan</button>
            </div>
            <small class="current-offset">Offset saat ini: <span id="currentPhOffset">0</span></small>
        </div>
        
        <!-- Kalibrasi Turbidity -->
        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:8px; font-weight:600;">💧 Kalibrasi Turbidity</label>
            <div class="offset-input-group">
                <input type="number" id="offsetTurbInput" placeholder="Contoh: -50 atau +30">
                <button onclick="saveTurbidityOffset()">Simpan</button>
            </div>
            <small class="current-offset">Offset saat ini: <span id="currentTurbOffset">0</span></small>
        </div>
        
        <div class="modal-footer-buttons">
            <button class="reset-btn" onclick="resetAllCalibrationConfirm()">🔄 Reset Semua</button>
            <button class="close-btn" onclick="closeKalibrasiModal()">Tutup</button>
        </div>
    </div>
</div>

<!-- TOAST -->
@if(session('success')) 
    <div class="toast success" id="toast">✅ {{ session('success') }}</div>
@endif
@if(session('error')) 
    <div class="toast error" id="toast">❌ {{ session('error') }}</div>
@endif

<!-- ==================== JAVASCRIPT ==================== -->
<script>
    // ==================== VARIABLES ====================
    let currentPh = null;
    let currentTurbidity = null;
    
    // ==================== LOAD DATA ====================
    document.addEventListener('DOMContentLoaded', function() {
        const toast = document.getElementById('toast');
        if (toast) {
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        loadRealtimeData();
        loadCalibrationStatus();
        
        setInterval(loadRealtimeData, 10000);
        setInterval(loadCalibrationStatus, 30000);
        
        const fotoInput = document.getElementById('fotoInput');
        const previewFoto = document.getElementById('previewFoto');
        if (fotoInput && previewFoto) {
            fotoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        previewFoto.src = event.target.result;
                        previewFoto.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
    
    // ==================== LOAD REALTIME DATA ====================
    async function loadRealtimeData() {
        try {
            // 🔥 PAKAI ENDPOINT /api/realtime (BUKAN /api/sensor/realtime)
            const response = await fetch('/api/realtime');
            const data = await response.json();
            
            if (data.success !== false) {
                // Update teks pH
                const phElement = document.getElementById('qualityPh');
                if (phElement) phElement.innerText = data.ph.toFixed(2);

                // Update status pH
                const phStatusElement = document.getElementById('qualityPhStatus');
                if (phStatusElement && data.ph_status) {
                    phStatusElement.innerText = capitalize(data.ph_status);
                    phStatusElement.className = 'quality-status ' + getStatusClass(data.ph_status);
                }

                // Update teks Turbidity
                const turbElement = document.getElementById('qualityTurb');
                if (turbElement) turbElement.innerText = data.turbidity + ' NTU';

                // Update status Turbidity
                const turbStatusElement = document.getElementById('qualityTurbStatus');
                if (turbStatusElement && data.turbidity_status) {
                    turbStatusElement.innerText = capitalize(data.turbidity_status);
                    turbStatusElement.className = 'quality-status ' + getStatusClass(data.turbidity_status);
                }

                // Update Info Offset
                const phCalibInfo = document.getElementById('phCalibInfo');
                if (phCalibInfo && data.ph_offset != 0) {
                    phCalibInfo.innerHTML = '🔧 Offset: ' + (data.ph_offset > 0 ? '+' : '') + data.ph_offset;
                } else if (phCalibInfo) {
                    phCalibInfo.innerHTML = '';
                }
                
                const turbCalibInfo = document.getElementById('turbCalibInfo');
                if (turbCalibInfo && data.turbidity_offset != 0) {
                    turbCalibInfo.innerHTML = '🔧 Offset: ' + (data.turbidity_offset > 0 ? '+' : '') + data.turbidity_offset;
                } else if (turbCalibInfo) {
                    turbCalibInfo.innerHTML = '';
                }
            }
        } catch (error) {
            console.error('Error loading realtime data:', error);
        }
    }
    
    // ==================== LOAD CALIBRATION STATUS ====================
    async function loadCalibrationStatus() {
        try {
            const response = await fetch('/api/calibration/status');
            const data = await response.json();
            
            if (data.success) {
                const statusDiv = document.getElementById('calibrationStatus');
                if (statusDiv) {
                    if (data.ph_offset != 0 || data.turbidity_offset != 0) {
                        statusDiv.innerHTML = '✅ Terkalibrasi (pH: ' + (data.ph_offset > 0 ? '+' : '') + data.ph_offset + ', Turbidity: ' + (data.turbidity_offset > 0 ? '+' : '') + data.turbidity_offset + ')';
                        statusDiv.style.color = '#10b981';
                        statusDiv.style.background = '#d1fae5';
                    } else {
                        statusDiv.innerHTML = '⚠️ Sensor belum dikalibrasi, disarankan kalibrasi untuk akurasi optimal';
                        statusDiv.style.color = '#d97706';
                        statusDiv.style.background = '#fed7aa';
                    }
                }
            }
        } catch (error) {
            console.error('Error loading calibration status:', error);
        }
    }
    
    // ==================== MODAL KALIBRASI ====================
    async function openKalibrasiModal() {
        document.getElementById('kalibrasiModal').style.display = 'flex';
        await loadCurrentValuesForModal();
        await loadOffsetStatus();
    }
    
    function closeKalibrasiModal() {
        document.getElementById('kalibrasiModal').style.display = 'none';
    }
    
    async function loadCurrentValuesForModal() {
        try {
            // 🔥 PAKAI ENDPOINT /api/realtime (BUKAN /api/sensor/realtime)
            const response = await fetch('/api/realtime');
            const data = await response.json();
            
            if (data.success !== false) {
                document.getElementById('modalCurrentPh').innerText = data.ph.toFixed(2);
                document.getElementById('modalCurrentTurb').innerText = data.turbidity + ' NTU';
            }
        } catch (error) {
            console.error('Error loading current values:', error);
        }
    }
    
    async function loadOffsetStatus() {
        try {
            const response = await fetch('/api/calibration/status');
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('currentPhOffset').innerText = data.ph_offset;
                document.getElementById('currentTurbOffset').innerText = data.turbidity_offset;
            }
        } catch (error) {
            console.error('Error loading offset status:', error);
        }
    }
    
    // ==================== SAVE OFFSET ====================
    async function savePHOffset() {
        const offset = document.getElementById('offsetPhInput').value;
        
        if (!offset) {
            showToast('Masukkan offset pH terlebih dahulu!', 'error');
            return;
        }
        
        try {
            const response = await fetch('/api/calibration/ph-offset', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ offset: parseFloat(offset) })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast(result.message, 'success');
                loadOffsetStatus();
                loadRealtimeData();
                document.getElementById('offsetPhInput').value = '';
            } else {
                showToast('Gagal: ' + result.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Gagal menyimpan offset pH', 'error');
        }
    }
    
    async function saveTurbidityOffset() {
        const offset = document.getElementById('offsetTurbInput').value;
        
        if (!offset) {
            showToast('Masukkan offset turbidity terlebih dahulu!', 'error');
            return;
        }
        
        try {
            const response = await fetch('/api/calibration/turbidity-offset', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ offset: parseFloat(offset) })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast(result.message, 'success');
                loadOffsetStatus();
                loadRealtimeData();
                document.getElementById('offsetTurbInput').value = '';
            } else {
                showToast('Gagal: ' + result.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Gagal menyimpan offset turbidity', 'error');
        }
    }
    
    // ==================== RESET KALIBRASI ====================
    function resetAllCalibrationConfirm() {
        if (!confirm('⚠️ Apakah Anda yakin ingin mereset semua kalibrasi? Nilai offset akan kembali ke 0.')) {
            return;
        }
        resetAllCalibration();
    }

    async function resetAllCalibration() {
        try {
            const response = await fetch('/api/calibration/reset', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ type: 'all' })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast(result.message, 'success');
                loadRealtimeData();
                loadCalibrationStatus();
            } else {
                showToast('Gagal reset: ' + result.message, 'error');
            }
        } catch (error) {
            console.error('Error reset kalibrasi:', error);
            showToast('❌ Terjadi kesalahan saat reset', 'error');
        }
    }
    
    // ==================== BUDIDAYA FUNCTIONS ====================
    function openBudidayaModal() {
        document.getElementById('budidayaModal').style.display = 'flex';
    }
    
    function closeBudidayaModal() {
        document.getElementById('budidayaModal').style.display = 'none';
    }
    
    function resetBudidaya() {
        if (confirm('⚠️ Apakah Anda yakin ingin mereset masa budidaya ke hari ini?')) {
            fetch('/budidaya/reset', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('Gagal reset budidaya', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan', 'error');
            });
        }
    }
    
    // ==================== MODAL FUNCTIONS ====================
    function openEditModal() {
        document.getElementById('editModal').style.display = 'flex';
    }
    
    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }
    
    // ==================== HELPER FUNCTIONS ====================
    function capitalize(str) {
        if (!str) return 'Normal';
        const map = {
            'baik': 'Normal',
            'aman': 'Normal',
            'normal': 'Normal',
            'peringatan': 'Peringatan',
            'warning': 'Peringatan',
            'bahaya': 'Bahaya',
            'danger': 'Bahaya'
        };
        return map[str.toLowerCase()] || 'Normal';
    }
    
    function getStatusClass(status) {
        const map = { 
            'baik': 'normal',
            'aman': 'normal', 
            'normal': 'normal',
            'peringatan': 'warning',
            'warning': 'warning',
            'bahaya': 'danger',
            'danger': 'danger'
        };
        return map[status?.toLowerCase()] || 'normal';
    }
    
    function setDefaultValues() {
        const phElement = document.getElementById('qualityPh');
        const phStatusElement = document.getElementById('qualityPhStatus');
        const turbElement = document.getElementById('qualityTurb');
        const turbStatusElement = document.getElementById('qualityTurbStatus');
        
        if (phElement) phElement.innerText = '7.0';
        if (phStatusElement) {
            phStatusElement.innerText = 'Normal';
            phStatusElement.className = 'quality-status normal';
        }
        if (turbElement) turbElement.innerText = '30 NTU';
        if (turbStatusElement) {
            turbStatusElement.innerText = 'Normal';
            turbStatusElement.className = 'quality-status normal';
        }
    }
    
    function showToast(message, type = 'success') {
        let toast = document.getElementById('dynamicToast');
        if (toast) toast.remove();
        
        toast = document.createElement('div');
        toast.id = 'dynamicToast';
        toast.className = 'toast ' + type;
        toast.innerHTML = '<div>' + (type === 'success' ? '✅' : '❌') + ' ' + message + '</div>';
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Close modal on outside click
    window.onclick = function(event) {
        const modals = ['editModal', 'budidayaModal', 'kalibrasiModal'];
        modals.forEach(function(modalId) {
            const modal = document.getElementById(modalId);
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    }
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.querySelector('form');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    return fetch('/api/update-session-profile', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    });
                }
                throw new Error('Gagal update profile');
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('✅ Profile berhasil diupdate', 'success');
                    sessionStorage.setItem('profile_updated', 'true');
                    setTimeout(function() {
                        window.location.href = '/';
                    }, 1000);
                }
            })
            .catch(function(error) {
                showToast('❌ ' + error.message, 'error');
            });
        });
    }
});

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.innerHTML = '<div class="toast-content"><span>' + (type === 'success' ? '✅' : '❌') + '</span><span>' + message + '</span></div>';
    document.body.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 3000);
}
</script>
@endsection