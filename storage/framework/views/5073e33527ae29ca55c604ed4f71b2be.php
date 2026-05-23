

<?php $__env->startSection('title', 'Profil Tambak'); ?>

<?php $__env->startSection('content'); ?>

<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/profile.css')); ?>">

<?php
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
    
    // 🔥 PAKAN HARI INI (dari database feeding_records) - dalam KG
    use App\Models\FeedingRecord;
    $pakanHariIniKg = FeedingRecord::whereDate('created_at', today())->sum('pakan_kg');
    $pakanHariIniGram = FeedingRecord::whereDate('created_at', today())->sum('target_gram');
?>

<div class="profile-container">

    <!-- HERO SECTION (Tanpa Button Edit) -->
    <div class="profile-hero">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="hero-icon">
                <img src="<?php echo e(asset('images/pengaturan.png')); ?>" alt="Profile Icon">
            </div>
            <div class="hero-text">
                <h1>Profil Tambak</h1>
                <p>Kelola informasi dan pantau perkembangan budidaya udang Anda</p>
            </div>
        </div>
    </div>

    <!-- STATS CARD (4 Card) -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">🦐</div>
            <div class="stat-info">
                <h3><?php echo e(number_format($populasi)); ?></h3>
                <p>Populasi Udang</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⚖️</div>
            <div class="stat-info">
                <h3><?php echo e(number_format($avgWeight, 2)); ?> <span>gram</span></h3>
                <p>Berat Rata-rata</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-info">
                <h3><?php echo e(number_format($biomassaKg, 2)); ?> <span>kg</span></h3>
                <p>Biomassa Total</p>
            </div>
        </div>
        <!-- 🔥 CARD PAKAN PER HARI (dalam KG, sinkron dengan Home) -->
        <div class="stat-card">
            <div class="stat-icon">🍽️</div>
            <div class="stat-info">
                <h3 id="pakanHariIniProfile"><?php echo e(number_format($pakanHariIniKg, 2)); ?> <span>kg</span></h3>
                <p>Pakan Hari Ini</p>
            </div>
        </div>
    </div>

    <!-- CONTENT GRID - 2 KOLOM (Informasi Tambak + Foto Tambak) -->
    <div class="content-grid-two">
        
        <!-- KIRI: Informasi Tambak (DENGAN TOMBOL EDIT DI BAWAH) -->
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
                    <div class="info-value"><?php echo e($profile->nama_tambak ?? '-'); ?></div>
                </div>
                <div class="info-row-elegant">
                    <div class="info-label">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Lokasi</span>
                    </div>
                    <div class="info-value"><?php echo e($profile->lokasi ?? '-'); ?></div>
                </div>
                <div class="info-row-elegant">
                    <div class="info-label">
                        <i class="fas fa-expand-alt"></i>
                        <span>Luas Tambak</span>
                    </div>
                    <div class="info-value"><?php echo e(number_format($profile->luas ?? 0, 0)); ?> m²</div>
                </div>
                <div class="info-row-elegant">
                    <div class="info-label">
                        <i class="fas fa-tint"></i>
                        <span>Tipe Tambak</span>
                    </div>
                    <div class="info-value"><?php echo e($profile->tipe_tambak ?? '-'); ?></div>
                </div>
                <div class="info-row-elegant">
                    <div class="info-label">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Tanggal Dibuat</span>
                    </div>
                    <div class="info-value"><?php echo e($profile->tanggal_dibuat ?? '-'); ?></div>
                </div>
            </div>
            <!-- 🔥 TOMBOL EDIT DI BAWAH CARD -->
            <div class="card-footer-elegant">
                <button class="edit-btn-full" onclick="openEditModal()">
                    <i class="fas fa-pen"></i> Edit Profil Tambak
                </button>
            </div>
        </div>

        <!-- KANAN: Foto Tambak (ELEGAN) -->
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
                <img src="<?php echo e($profile && $profile->foto_tambak ? asset('storage/' . $profile->foto_tambak) : asset('images/tambak4.jpeg')); ?>" alt="Tambak">
                <div class="photo-overlay">
                    <span class="photo-badge">
                        <i class="fas fa-image"></i> Tambak
                    </span>
                </div>
            </div>
        </div>

    </div>

   <!-- BOTTOM GRID: Kualitas Air & Budidaya -->
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
                    <span class="quality-value" id="qualityPh">7.82</span>
                    <span class="quality-status" id="qualityPhStatus">Normal</span>
                </div>
            </div>
            <div class="quality-item">
                <div class="quality-icon">⚪</div>
                <div class="quality-info">
                    <span class="quality-label">Turbidity</span>
                    <span class="quality-value" id="qualityTurb">35 NTU</span>
                    <span class="quality-status" id="qualityTurbStatus">Normal</span>
                </div>
            </div>
        </div>
      <div class="param-buttons">
    <button onclick="kalibrasiPH()" class="param-btn">
        <i class="fas fa-microscope"></i> Kalibrasi pH
    </button>
    <button onclick="kalibrasiTurbidity()" class="param-btn">
        <i class="fas fa-microscope"></i> Kalibrasi Turbidity
    </button>
</div>
    </div>

    <!-- MASA BUDIDAYA CARD -->
    <div class="budidaya-card">
        <div class="card-header">
            <span class="card-icon">📅</span>
            <h3>Masa Budidaya</h3>
        </div>
        <?php if(!$profile || !$start): ?>
            <button class="start-btn" onclick="openBudidayaModal()">
                <i class="fas fa-play"></i> Mulai Budidaya
            </button>
        <?php else: ?>
            <div class="budidaya-content">
                <div class="budidaya-info">
                    <div class="budidaya-age">
                        <span class="age-number"><?php echo e($days); ?></span>
                        <span class="age-label">Hari</span>
                    </div>
                    <div class="budidaya-detail">
                        <p>📊 Umur: <strong><?php echo e($umurMinggu); ?> Minggu</strong></p>
                        <p>📅 Mulai: <strong><?php echo e($profile->tanggal_mulai_budidaya); ?></strong></p>
                        <p>🎯 Panen: <strong><?php echo e($estimasiPanen); ?></strong></p>
                    </div>
                </div>
                <div class="progress-container">
                    <div class="progress-label">
                        <span>📈 Progress Budidaya</span>
                        <span><?php echo e(round($percent)); ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo e($percent); ?>%"></div>
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
        <?php endif; ?>
    </div>

</div>

<!-- MODAL EDIT PROFILE -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>✏️ Edit Profil Tambak</h3>
        <form action="<?php echo e(route('profile.update')); ?>" method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <input type="text" name="nama_tambak" placeholder="Nama Tambak" value="<?php echo e($profile->nama_tambak ?? ''); ?>">
            <input type="text" name="lokasi" placeholder="Lokasi" value="<?php echo e($profile->lokasi ?? ''); ?>">
            <input type="number" step="0.01" name="luas" placeholder="Luas Tambak (m²)" value="<?php echo e($profile->luas ?? ''); ?>">
            <input type="text" name="tipe_tambak" placeholder="Tipe Tambak" value="<?php echo e($profile->tipe_tambak ?? ''); ?>">
            <input type="number" name="populasi" placeholder="Populasi Udang (ekor)" value="<?php echo e($populasi); ?>">
            <input type="number" step="0.01" name="avg_weight" placeholder="Berat Rata-rata (gram/ekor)" value="<?php echo e($avgWeight); ?>">
            <input type="date" name="tanggal_dibuat" value="<?php echo e($profile->tanggal_dibuat ?? ''); ?>">
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

<!-- MODAL BUDIDAYA -->
<div id="budidayaModal" class="modal">
    <div class="modal-content">
        <h3>📅 Mulai / Edit Budidaya</h3>
        <form action="<?php echo e(route('budidaya.start')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <label>Tanggal Mulai Budidaya</label>
            <input type="date" name="tanggal_mulai_budidaya" value="<?php echo e($profile->tanggal_mulai_budidaya ?? ''); ?>" required>
            <div class="modal-action">
                <button type="button" onclick="closeBudidayaModal()">Batal</button>
                <button type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- TOAST -->
<?php if(session('success')): ?> 
    <div class="toast success" id="toast">✅ <?php echo e(session('success')); ?></div>
<?php endif; ?>
<?php if(session('error')): ?> 
    <div class="toast error" id="toast">❌ <?php echo e(session('error')); ?></div>
<?php endif; ?>

<script>
    // Auto reload setelah toast success
    document.addEventListener('DOMContentLoaded', function() {
        const toast = document.getElementById('toast');
        if (toast && toast.classList.contains('success')) {
            setTimeout(() => {
                location.reload();
            }, 2000);
        }
        
        // 🔥 Update pakan hari ini secara real-time
        updatePakanHariIni();
        setInterval(updatePakanHariIni, 10000);
    });
    
    // 🔥 Fungsi update pakan hari ini (dalam KG)
    function updatePakanHariIni() {
        fetch('/api/feeding/today')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const pakanElement = document.getElementById('pakanHariIniProfile');
                    if (pakanElement) {
                        pakanElement.innerHTML = data.total_kg.toFixed(2) + ' <span>kg</span>';
                    }
                }
            })
            .catch(error => console.error('Error:', error));
    }
    
    function openEditModal() {
        document.getElementById('editModal').style.display = 'flex';
    }
    
    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }
    
    function openBudidayaModal() {
        document.getElementById('budidayaModal').style.display = 'flex';
    }
    
    function closeBudidayaModal() {
        document.getElementById('budidayaModal').style.display = 'none';
    }
    
    function kalibrasi(type) {
        showToast('Fitur kalibrasi ' + type + ' akan segera tersedia', 'info');
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
    
    function showToast(message, type) {
        let toast = document.getElementById('toast');
        if (toast) toast.remove();
        toast = document.createElement('div');
        toast.id = 'toast';
        toast.className = `toast ${type}`;
        toast.innerHTML = `<div class="toast-content">${type === 'success' ? '✅' : (type === 'error' ? '❌' : 'ℹ️')} ${message}</div>`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
    
    // Preview foto
    document.getElementById('fotoInput')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('previewFoto');
        if (file && preview) {
            const reader = new FileReader();
            reader.onload = function(event) {
                preview.src = event.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
</script>
<script>
function kalibrasiPH() {
    fetch('/api/realtime')
        .then(res => res.json())
        .then(data => {
            const currentPh = data.ph;
            if (confirm(`Kalibrasi pH dari ${currentPh} ke 7.0?`)) {
                fetch('/api/calibrate/ph', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ desired_value: 7.0, current_value: currentPh })
                })
                .then(res => res.json())
                .then(result => {
                    alert(result.message);
                    if (result.success) location.reload();
                });
            }
        });
}

function kalibrasiTurbidity() {
    fetch('/api/realtime')
        .then(res => res.json())
        .then(data => {
            const currentTurb = data.turbidity;
            if (confirm(`Kalibrasi Turbidity dari ${currentTurb} ke 30 NTU?`)) {
                fetch('/api/calibrate/turbidity', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ desired_value: 30, current_value: currentTurb })
                })
                .then(res => res.json())
                .then(result => {
                    alert(result.message);
                    if (result.success) location.reload();
                });
            }
        });
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('footbar.utama', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Skirpsi\tambak_udang\resources\views/dashboard/profile.blade.php ENDPATH**/ ?>