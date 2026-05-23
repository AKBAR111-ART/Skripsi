@extends('footbar.utama')

@php
$rule = $rule ?? [
    'ph_min_good' => 7.5,
    'ph_max_good' => 8.5,
    'ph_min_warning' => 7.0,
    'ph_max_warning' => 7.4,
    'ph_min_warning_high' => 8.6,
    'ph_max_warning_high' => 8.9,
    'ph_danger_low' => 6.5,
    'ph_danger_high' => 9.0,
    'turbidity_min_good' => 25,
    'turbidity_max_good' => 50,
    'turbidity_min_warning' => 51,
    'turbidity_max_warning' => 70,
    'turbidity_danger_low' => 10,
    'turbidity_danger_high' => 70,
];
@endphp

@section('title', 'Halaman Pengaturan')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/pengaturan.css') }}">
@endpush

@section('content')
<div class="settings-container">
    
    <!-- ALERT PERINGATAN DATA KOSONG -->
    <div id="dataAlert" class="data-alert" style="display: none;">
        <div class="alert-warning-premium">
            <i class="fas fa-exclamation-triangle"></i>
            <div class="alert-content">
                <strong>⚠️ Data Belum Lengkap!</strong>
                <ul id="alertList" style="margin: 8px 0 0 20px;"></ul>
            </div>
            <button class="alert-close" onclick="closeDataAlert()">✖</button>
        </div>
    </div>
    
    <!-- HEADER PREMIUM -->
    <div class="settings-header">
        <div class="settings-header-left">
            <div class="settings-header-icon">⚙️</div>
            <div class="settings-header-text">
                <h1>Pengaturan Rekomendasi Pakan</h1>
                <p>Manajemen otomatis berbasis sensor tambak</p>
            </div>
        </div>
        <button class="btn-help-premium">❓ Bantuan</button>
    </div>
    
    <!-- GRID VARIABEL SENSOR & RULE ENGINE -->
    <div class="settings-grid">
        
        <div class="settings-card">
            <div class="card-title"><i class="fas fa-microchip"></i><h2>Variabel Sensor</h2></div>
            <table class="settings-table">
                <thead><tr><th>Variabel</th><th>Kondisi</th><th>Rentang</th><th></th></tr></thead>
                <tbody>
                    <tr><td>💧 pH</td><td><span class="badge-premium badge-good">Baik</span></td><td id="ph_baik_text">{{ $rule['ph_min_good'] ?? '7.5' }} - {{ $rule['ph_max_good'] ?? '8.5' }}</td><td><button class="btn-edit-premium open-edit">✏️</button></td></tr>
                    <tr><td>💧 pH</td><td><span class="badge-premium badge-warning">Peringatan</span></td><td id="ph_warn_text">{{ $rule['ph_min_warning'] ?? '7.0' }} - {{ $rule['ph_max_warning'] ?? '7.4' }}<br><small>atau {{ $rule['ph_min_warning_high'] ?? '8.6' }} - {{ $rule['ph_max_warning_high'] ?? '8.9' }}</small></td><td></td></tr>
                    <tr><td>💧 pH</td><td><span class="badge-premium badge-danger">Bahaya</span></td><td id="ph_bahaya_text">< {{ $rule['ph_danger_low'] ?? '6.5' }} atau > {{ $rule['ph_danger_high'] ?? '9.0' }}</td><td></td></tr>
                    <tr><td>⚪ Turbidity</td><td><span class="badge-premium badge-good">Baik</span></td><td id="tur_baik_text">{{ $rule['turbidity_min_good'] ?? '25' }} - {{ $rule['turbidity_max_good'] ?? '50' }}</td><td></td></tr>
                    <tr><td>⚪ Turbidity</td><td><span class="badge-premium badge-warning">Peringatan</span></td><td id="tur_warn_text">{{ $rule['turbidity_min_warning'] ?? '51' }} - {{ $rule['turbidity_max_warning'] ?? '70' }}</td><td></td></tr>
                    <tr><td>⚪ Turbidity</td><td><span class="badge-premium badge-danger">Bahaya</span></td><td id="tur_bahaya_text">< {{ $rule['turbidity_danger_low'] ?? '10' }} atau > {{ $rule['turbidity_danger_high'] ?? '70' }}</td><td></td></tr>
                </tbody>
            </table>
        </div>
        
        <div class="settings-card">
            <div class="card-title"><i class="fas fa-brain"></i><h2>Rule Engine</h2></div>
            <div class="rule-box-premium">
                <ul>
                    <li><i class="fas fa-check-circle" style="color:#10b981;"></i> Kondisi baik → pakan optimal</li>
                    <li><i class="fas fa-exclamation-triangle" style="color:#f59e0b;"></i> Kondisi sedang → pakan dikurangi 50%</li>
                    <li><i class="fas fa-skull-crosswalk" style="color:#ef4444;"></i> Kondisi buruk → pakan dihentikan</li>
                </ul>
            </div>
            <div class="rule-status-premium"><span class="dot-active"></span> Sistem Aktif</div>
        </div>
        
    </div>
    
    <!-- OUTPUT CARD -->
    <div class="output-card-premium">
        
        <div class="output-left-premium">
            <div class="card-title"><i class="fas fa-bell"></i><h2>⚡ Pengingat Anda</h2></div>
            
            <div class="form-group-premium">
                <label>👨‍🌾 Nama Penjaga</label>
                <div id="penjagaContainer"></div>
                <button type="button" id="addPenjaga" class="btn-add-premium">+ Tambah Penjaga</button>
            </div>
            
            <div class="form-group-premium">
                <label>📱 Nomor WhatsApp</label>
                <div id="waContainer"></div>
                <button type="button" id="addWa" class="btn-add-premium">+ Tambah Nomor WA</button>
            </div>
            
            <div class="form-group-premium">
                <label>⏰ Waktu Pakan</label>
                <div id="waktuContainer"></div>
                <button type="button" id="addWaktu" class="btn-add-premium">+ Tambah Waktu</button>
            </div>
            
            <div class="form-group-premium">
                <label>📅 Tanggal</label>
                <input type="date" id="tanggalInput" class="form-control-premium" value="{{ $pengaturan->tanggal ?? '' }}">
            </div>
            
            {{-- <div class="form-group-premium" style="margin-top: 20px;">
                <label>⏰ Jadwal Pengingat WhatsApp</label>
                <div id="jadwalContainer"></div>
                <div class="row" style="display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap;">
                    <input type="time" id="newJam" class="form-control-premium" placeholder="Jam" style="flex:1; min-width: 120px;">
                    <input type="text" id="newPesan" class="form-control-premium" placeholder="Pesan pengingat" style="flex:2; min-width: 200px;">
                    <input type="text" id="newTargetNomor" class="form-control-premium" placeholder="Nomor WA (pisah dengan koma)" style="flex:2; min-width: 200px;">
                    <button type="button" id="addJadwal" class="btn-add-premium" style="flex:0;">+ Tambah</button>
                </div>
                <small style="color: #6b7280;">Format nomor: 628123456789 (pisahkan dengan koma jika lebih dari satu)</small>
            </div> --}}
        </div>
        
        <div class="output-right-premium">
            <div class="card-title"><i class="fas fa-chart-line"></i><h2>📊 Monitoring Tambak</h2></div>
            
            <div class="form-group-premium">
                <label>💬 Template Pesan WhatsApp</label>
                <textarea id="templatePesan" class="form-control-premium" rows="4">{{ $pengaturan->template_pesan ?? '' }}</textarea>
                <small style="color:#6b7280;">Gunakan: @{{penjaga}} @{{waktu}} @{{tanggal}}</small>
            </div>
            
            <div class="premium-result-box">
                <h1 id="beratHighlight">0 kg</h1>
                <p>Rekomendasi Pakan</p>
            </div>
            
            <div class="info-preview">
                <p><b>Penjaga:</b> <span id="penjagaNow">{{ is_array($pengaturan->penjaga ?? null) ? implode(', ', $pengaturan->penjaga) : (is_string($pengaturan->penjaga ?? null) ? implode(', ', json_decode($pengaturan->penjaga, true) ?? []) : '-') }}</span></p>
                <p><b>WA:</b> <span id="waNow">{{ is_array($pengaturan->nomor_wa ?? null) ? implode(', ', $pengaturan->nomor_wa) : (is_string($pengaturan->nomor_wa ?? null) ? implode(', ', json_decode($pengaturan->nomor_wa, true) ?? []) : '-') }}</span></p>
                <p><b>Waktu:</b> <span id="waktuNow">{{ is_array($pengaturan->waktu ?? null) ? implode(', ', $pengaturan->waktu) : (is_string($pengaturan->waktu ?? null) ? implode(', ', json_decode($pengaturan->waktu, true) ?? []) : '-') }}</span></p>
                <p><b>Tanggal:</b> <span id="tanggalNow">{{ $pengaturan->tanggal ?? '-' }}</span></p>
            </div>
        </div>
        
    </div>
    
    <div class="settings-actions">
        <button class="btn-reset-premium" id="btnReset">Reset</button>
        <button class="btn-save-premium" id="btnSimpan">💾 Simpan Pengaturan</button>
    </div>
    
</div>

<!-- MODAL EDIT RULE ENGINE -->
<div id="editModal" class="modal-premium">
    <div class="modal-content-premium">
        <div class="modal-header-premium"><h2>⚙️ Edit Rule Engine</h2><span onclick="closeEdit()" class="close-btn">✖</span></div>
        <div class="modal-body-premium">
            <div class="rule-section">
                <h3><i class="fas fa-tint"></i> pH</h3>
                <div class="rule-row"><label>✅ Baik</label><div class="range-input"><input type="number" step="0.1" id="ph_baik_min" value="7.5"><span>-</span><input type="number" step="0.1" id="ph_baik_max" value="8.5"></div></div>
                <div class="rule-row"><label>⚠️ Peringatan Bawah</label><div class="range-input"><input type="number" step="0.1" id="ph_warn_min" value="7.0"><span>-</span><input type="number" step="0.1" id="ph_warn_max" value="7.4"></div></div>
                <div class="rule-row"><label>⚠️ Peringatan Atas</label><div class="range-input"><input type="number" step="0.1" id="ph_warn_min_high" value="8.6"><span>-</span><input type="number" step="0.1" id="ph_warn_max_high" value="8.9"></div></div>
                <div class="rule-row"><label>❌ Bahaya Rendah</label><input type="number" step="0.1" id="ph_danger_low" value="6.5"></div>
                <div class="rule-row"><label>❌ Bahaya Tinggi</label><input type="number" step="0.1" id="ph_danger_high" value="9.0"></div>
            </div>
            <div class="rule-section">
                <h3><i class="fas fa-water"></i> Turbidity (NTU)</h3>
                <div class="rule-row"><label>✅ Baik</label><div class="range-input"><input type="number" id="tur_baik_min" value="25"><span>-</span><input type="number" id="tur_baik_max" value="50"></div></div>
                <div class="rule-row"><label>⚠️ Peringatan</label><div class="range-input"><input type="number" id="tur_warn_min" value="51"><span>-</span><input type="number" id="tur_warn_max" value="70"></div></div>
                <div class="rule-row"><label>❌ Bahaya Rendah</label><input type="number" id="tur_danger_low" value="10"></div>
                <div class="rule-row"><label>❌ Bahaya Tinggi</label><input type="number" id="tur_danger_high" value="70"></div>
            </div>
        </div>
        <div class="modal-footer-premium"><button class="btn-modal-cancel" onclick="closeEdit()">Batal</button><button class="btn-modal-save" id="saveRuleBtn">💾 Simpan Rule</button></div>
    </div>
</div>

<script>
    window.rule_sensor = @json($rule);
    window.pengaturanData = @json($pengaturan);
    window.rule = @json($rule);
</script>

<script src="{{ asset('js/pengaturan.js') }}"></script>
@endsection