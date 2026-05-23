

<?php $__env->startSection('title', 'Premium History Monitoring'); ?>

<?php $__env->startPush('styles'); ?>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?php echo e(asset('css/history.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
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
                <?php $__currentLoopData = $weeksData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="week-card <?php echo e(!$week['has_data'] ? 'empty' : ''); ?>" 
                     data-week="<?php echo e($week['week']); ?>"
                     onclick="selectWeek(<?php echo e($week['week']); ?>)">
                    <?php
                        $badgeClass = 'normal';
                        if (!$week['has_data']) $badgeClass = 'mendatang';
                        elseif ($week['status'] == 'Perhatian') $badgeClass = 'perhatian';
                        elseif ($week['status'] == 'Kritis') $badgeClass = 'kritis';
                    ?>
                    <div class="week-badge <?php echo e($badgeClass); ?>"><?php echo e($week['status'] ?? 'Mendatang'); ?></div>
                    <div class="week-title"><?php echo e($week['period']); ?></div>
                    <div class="week-stats">
                        <?php if($week['has_data']): ?>
                            <div><small>pH</small><br><strong id="weekPh-<?php echo e($week['week']); ?>"><?php echo e($week['avg_ph'] ?? '-'); ?></strong></div>
                            <div><small>Turb</small><br><strong id="weekTurb-<?php echo e($week['week']); ?>"><?php echo e($week['avg_turbidity'] ?? '-'); ?></strong></div>
                            <div><small>Pakan</small><br><strong id="weekFeed-<?php echo e($week['week']); ?>"><?php echo e(number_format($week['total_feed'] ?? 0, 1)); ?>kg</strong></div>
                        <?php else: ?>
                            <div><small>pH</small><br><span class="empty-value">--</span></div>
                            <div><small>Turb</small><br><span class="empty-value">--</span></div>
                            <div><small>Pakan</small><br><span class="empty-value">--</span></div>
                        <?php endif; ?>
                    </div>
                    <div style="margin-top: 10px; font-size: 11px; color: #64748b;">
                        <i class="fas fa-calendar-alt"></i> <?php echo e($week['date_range'] ?? ''); ?>

                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Data dari server
    window.historyData = {
        weeksData: <?php echo json_encode($weeksData, 15, 512) ?>,
        currentWeek: <?php echo e($currentMinggu ?? 1); ?>,
        biomassaKg: <?php echo e($biomassaKg ?? 0); ?>

    };
</script>
<script src="<?php echo e(asset('js/history.js')); ?>"></script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('footbar.utama', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Skirpsi\tambak_udang\resources\views/dashboard/history.blade.php ENDPATH**/ ?>