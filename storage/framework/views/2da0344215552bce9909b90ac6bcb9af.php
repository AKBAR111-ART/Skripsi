

<?php
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
?>

<?php $__env->startSection('title', 'Halaman Pengaturan'); ?>

<?php $__env->startPush('styles'); ?>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?php echo e(asset('css/pengaturan.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
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
                    <tr><td>💧 pH</td><td><span class="badge-premium badge-good">Baik</span></td><td id="ph_baik_text"><?php echo e($rule['ph_min_good'] ?? '7.5'); ?> - <?php echo e($rule['ph_max_good'] ?? '8.5'); ?></td><td><button class="btn-edit-premium open-edit">✏️</button></td></tr>
                    <tr><td>💧 pH</td><td><span class="badge-premium badge-warning">Peringatan</span></td><td id="ph_warn_text"><?php echo e($rule['ph_min_warning'] ?? '7.0'); ?> - <?php echo e($rule['ph_max_warning'] ?? '7.4'); ?><br><small>atau <?php echo e($rule['ph_min_warning_high'] ?? '8.6'); ?> - <?php echo e($rule['ph_max_warning_high'] ?? '8.9'); ?></small></td><td></td></tr>
                    <tr><td>💧 pH</td><td><span class="badge-premium badge-danger">Bahaya</span></td><td id="ph_bahaya_text">< <?php echo e($rule['ph_danger_low'] ?? '6.5'); ?> atau > <?php echo e($rule['ph_danger_high'] ?? '9.0'); ?></td><td></td></tr>
                    <tr><td>⚪ Turbidity</td><td><span class="badge-premium badge-good">Baik</span></td><td id="tur_baik_text"><?php echo e($rule['turbidity_min_good'] ?? '25'); ?> - <?php echo e($rule['turbidity_max_good'] ?? '50'); ?></td><td></td></tr>
                    <tr><td>⚪ Turbidity</td><td><span class="badge-premium badge-warning">Peringatan</span></td><td id="tur_warn_text"><?php echo e($rule['turbidity_min_warning'] ?? '51'); ?> - <?php echo e($rule['turbidity_max_warning'] ?? '70'); ?></td><td></td></tr>
                    <tr><td>⚪ Turbidity</td><td><span class="badge-premium badge-danger">Bahaya</span></td><td id="tur_bahaya_text">< <?php echo e($rule['turbidity_danger_low'] ?? '10'); ?> atau > <?php echo e($rule['turbidity_danger_high'] ?? '70'); ?></td><td></td></tr>
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
    
    <!-- OUTPUT CARD: PENGINGAT WHATSAPP & MONITORING TAMBAK -->
    <div class="output-card-premium">
        
        <!-- CARD PENGINGAT WHATSAPP -->
        <div class="output-left-premium">
            <div class="card-title">
                <i class="fas fa-bell"></i>
                <h2>⚡ Pengingat WhatsApp</h2>
            </div>
            
            <!-- TANGGAL MULAI -->
            <div class="form-group-premium">
                <label><i class="fas fa-calendar-alt"></i> 📅 Tanggal Mulai Pengingat</label>
                <input type="date" id="tanggalInput" class="form-control-premium" value="<?php echo e($pengaturan->tanggal ?? ''); ?>">
                <small class="form-help">Pengingat akan mulai aktif pada tanggal ini</small>
            </div>
            
            <!-- JADWAL PENGINGAT WHATSAPP -->
            <div class="form-group-premium">
                <label><i class="fab fa-whatsapp"></i> ⏰ Jadwal Pengingat WhatsApp</label>
                
                <div id="jadwalContainer" class="jadwal-container">
                    <?php if(isset($jadwalList) && count($jadwalList) > 0): ?>
                        <?php $__currentLoopData = $jadwalList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jadwal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="jadwal-item" data-id="<?php echo e($jadwal->id); ?>">
                            <div class="jadwal-info">
                                <span class="jadwal-time"><i class="far fa-clock"></i> <?php echo e($jadwal->jam); ?></span>
                                <span class="jadwal-message"><i class="fas fa-comment-dots"></i> <?php echo e($jadwal->pesan); ?></span>
                                <span class="jadwal-target"><i class="fab fa-whatsapp"></i> 
                                    <?php if(is_array($jadwal->target_nomor)): ?>
                                        <?php echo e(implode(', ', $jadwal->target_nomor)); ?>

                                    <?php else: ?>
                                        <?php echo e($jadwal->target_nomor); ?>

                                    <?php endif; ?>
                                </span>
                                <span class="jadwal-status <?php echo e($jadwal->is_sent ? 'sent' : 'pending'); ?>">
                                    <?php echo e($jadwal->is_sent ? '✅ Terkirim' : '⏳ Pending'); ?>

                                </span>
                            </div>
                            <button class="jadwal-delete" onclick="deleteJadwal(<?php echo e($jadwal->id); ?>)">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <div class="jadwal-empty">
                            <i class="fas fa-bell-slash"></i> Belum ada jadwal. Tambahkan di bawah.
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- FORM TAMBAH JADWAL -->
                <div class="jadwal-add-form">
                    <div class="jadwal-input-group">
                        <input type="time" id="newJam" class="form-control-premium" placeholder="Jam (contoh: 08:00)">
                        <input type="text" id="newPesan" class="form-control-premium" placeholder="Pesan pengingat">
                        <input type="text" id="newTargetNomor" class="form-control-premium" placeholder="Nomor WA (contoh: 628123456789)">
                        <button type="button" id="addJadwal" class="btn-add-premium">
                            <i class="fas fa-plus"></i> Tambah
                        </button>
                    </div>
                    <small class="form-help">
                        <i class="fas fa-info-circle"></i> 
                        Setiap jadwal akan mengirim WA otomatis ke nomor yang ditentukan. 
                        Format nomor: 628123456789 (awali 62, tanpa 0). Pisahkan dengan koma untuk multiple nomor.
                    </small>
                </div>
            </div>
            
            <!-- TEMPLATE PESAN DEFAULT -->
            <div class="form-group-premium">
                <label><i class="fas fa-edit"></i> 💬 Template Pesan Default</label>
                <textarea id="templatePesan" class="form-control-premium" rows="2" placeholder="Contoh: Waktunya memberi pakan untuk udang"><?php echo e($pengaturan->template_pesan ?? 'Waktunya memberi pakan untuk udang'); ?></textarea>
                <small class="form-help">
                    <i class="fas fa-code"></i> Gunakan <code>{{waktu}}</code> untuk menampilkan jam
                </small>
            </div>
        </div>
        
        <!-- CARD MONITORING TAMBAK -->
        <div class="output-right-premium">
            <div class="card-title">
                <i class="fas fa-chart-line"></i>
                <h2>📊 Monitoring Tambak</h2>
            </div>
            
            <!-- PREVIEW PENGINGAT -->
            <div class="preview-card">
                <div class="preview-header">
                    <i class="fas fa-eye"></i>
                    <span>Preview Pengingat</span>
                </div>
                <div class="preview-content">
                    <div class="preview-item">
                        <span class="preview-label">📅 Tanggal Mulai:</span>
                        <span class="preview-value" id="tanggalNow">-</span>
                    </div>
                    <div class="preview-item">
                        <span class="preview-label">⏰ Total Jadwal:</span>
                        <span class="preview-value" id="totalJadwalNow">0</span>
                    </div>
                </div>
            </div>
            
            <!-- REKOMENDASI PAKAN -->
            <div class="recommendation-card">
                <div class="recommendation-icon">
                    <i class="fas fa-fish"></i>
                </div>
                <div class="recommendation-content">
                    <div class="recommendation-label">Rekomendasi Pakan</div>
                    <div class="recommendation-value" id="beratHighlight">
                        <span class="value-number">0</span>
                        <span class="value-unit">kg</span>
                    </div>
                    <div class="recommendation-status" id="recommendationStatus">
                        <i class="fas fa-circle"></i> Berdasarkan kondisi air terkini
                    </div>
                </div>
            </div>
            
            <!-- STATUS SENSOR TERKINI -->
            <div class="sensor-status-card">
                <div class="sensor-status-header">
                    <i class="fas fa-microchip"></i>
                    <span>Status Sensor Terkini</span>
                    <span class="live-badge">LIVE</span>
                </div>
                <div id="statusBox" class="sensor-status-content">
                    <div class="sensor-status-loading">Memuat data sensor...</div>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- TOMBOL AKSI -->
    <div class="settings-actions">
        <button class="btn-reset-premium" id="btnReset">
            <i class="fas fa-undo-alt"></i> Reset
        </button>
        <button class="btn-save-premium" id="btnSimpan">
            <i class="fas fa-save"></i> Simpan Pengaturan
        </button>
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
    window.rule_sensor = <?php echo json_encode($rule, 15, 512) ?>;
    window.pengaturanData = <?php echo json_encode($pengaturan, 15, 512) ?>;
    window.rule = <?php echo json_encode($rule, 15, 512) ?>;
</script>

<script src="<?php echo e(asset('js/pengaturan.js')); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('footbar.utama', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Skirpsi\tambak_udang\resources\views/dashboard/pengaturan.blade.php ENDPATH**/ ?>