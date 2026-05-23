

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

        <!-- STATISTIK PREMIUM -->
        <div class="stats-premium-grid">
            <div class="stat-premium-card">
                <div class="stat-icon">📊</div>
                <div class="stat-value" id="statPh"><?php echo e($overallStats['avg_ph'] ?? '7.0'); ?></div>
                <div class="stat-label">Rata-rata pH</div>
            </div>
            <div class="stat-premium-card">
                <div class="stat-icon">💧</div>
                <div class="stat-value" id="statTurb"><?php echo e($overallStats['avg_turbidity'] ?? '12'); ?> <span style="font-size:14px;">NTU</span></div>
                <div class="stat-label">Rata-rata Kekeruhan</div>
            </div>
            <div class="stat-premium-card">
                <div class="stat-icon">🍽️</div>
                <div class="stat-value" id="statFeed"><?php echo e(number_format($overallStats['total_feed_kg'] ?? 0, 1)); ?> <span style="font-size:14px;">kg</span></div>
                <div class="stat-label">Total Pakan</div>
            </div>
            <div class="stat-premium-card">
                <div class="stat-icon">🏆</div>
                <div class="stat-value" id="statSuccess"><?php echo e($overallStats['success_rate'] ?? '85'); ?>%</div>
                <div class="stat-label">Tingkat Keberhasilan</div>
            </div>
        </div>

        <!-- TIMELINE BUDIDAYA -->
        <div class="timeline-premium">
            <div class="timeline-header">
                <div>
                    <h2><i class="fas fa-chart-line"></i> Timeline Budidaya</h2>
                    <p>Klik minggu untuk melihat data harian · <span id="currentWeekInfo">Minggu <?php echo e($currentMinggu ?? 1); ?> sedang berjalan</span></p>
                </div>
            </div>
            <div class="timeline-track" id="timelineTrack">
                <?php $__currentLoopData = $weeksData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="week-card <?php echo e(!$week['has_data'] ? 'empty' : ''); ?> <?php echo e(isset($week['is_current']) && $week['is_current'] ? 'active' : ''); ?>" 
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
                            <div><small>pH</small><br><strong><?php echo e($week['avg_ph'] ?? '-'); ?></strong></div>
                            <div><small>Turb</small><br><strong><?php echo e($week['avg_turbidity'] ?? '-'); ?></strong></div>
                            <div><small>Pakan</small><br><strong><?php echo e(number_format($week['total_feed'] ?? 0, 1)); ?>kg</strong></div>
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

        <!-- DATA MONITORING HARIAN (PER HARI DALAM MINGGU) -->
        <div class="daily-card">
            <div class="card-header-daily">
                <h3><i class="fas fa-calendar-day"></i> Data Monitoring Harian - <span id="dailyWeekTitle">Minggu <?php echo e($currentMinggu ?? 1); ?></span></h3>
            </div>
            <div style="overflow-x: auto;">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Hari</th>
                            <th>Rata-rata pH</th>
                            <th>Rata-rata Kekeruhan (NTU)</th>
                            <th>Total Pakan (kg)</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="dailyTableBody">
                        <?php if(count($dailyData) > 0): ?>
                            <?php $__currentLoopData = $dailyData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><strong><?php echo e($day['date']); ?></strong></td>
                                <td><?php echo e($day['day_name']); ?></div>
                                <td><?php echo e($day['avg_ph']); ?></div>
                                <td><?php echo e($day['avg_turbidity']); ?></div>
                                <td><?php echo e(number_format($day['total_feed'], 1)); ?></div>
                                <td>
                                    <?php
                                        $statusClass = 'good';
                                        if ($day['status'] == 'Perhatian') $statusClass = 'warning';
                                        if ($day['status'] == 'Kritis') $statusClass = 'danger';
                                    ?>
                                    <span class="status-badge <?php echo e($statusClass); ?>"><?php echo e($day['status']); ?></span>
                                </div>
                                <td>
                                    <button class="btn-detail" onclick="showDetail('<?php echo e($day['date']); ?>', '<?php echo e($day['day_name']); ?>')">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                </div>
                             </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center">Belum ada data monitoring</div></div>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- MODAL DETAIL PER HARI -->
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ============================================
// PREMIUM HISTORY JS - FINAL VERSION
// ============================================

let weeksData = [];
let currentWeek = 1;
let dailyDataCache = {};

// ==================== INITIALIZE ====================
function initHistoryData() {
    if (window.historyData) {
        weeksData = window.historyData.weeksData || [];
        currentWeek = window.historyData.currentWeek || 1;
        renderTimeline();
        updateStatCards(currentWeek);
        
        // Load daily data untuk minggu saat ini
        loadDailyDataForWeek(currentWeek);
    }
}

// ==================== RENDER TIMELINE ====================
function renderTimeline() {
    const track = document.getElementById('timelineTrack');
    if (!track || !weeksData.length) return;
    
    track.innerHTML = weeksData.map(week => {
        let badgeClass = 'normal';
        if (!week.has_data) badgeClass = 'mendatang';
        else if (week.status == 'Perhatian') badgeClass = 'perhatian';
        else if (week.status == 'Kritis') badgeClass = 'kritis';
        
        return `
            <div class="week-card ${week.week == currentWeek ? 'active' : ''} ${!week.has_data ? 'empty' : ''}" 
                 onclick="selectWeek(${week.week})">
                <div class="week-badge ${badgeClass}">${week.status || 'Mendatang'}</div>
                <div class="week-title">${week.period}</div>
                <div class="week-stats">
                    ${week.has_data ? `
                        <div><small>pH</small><br><strong>${week.avg_ph || '-'}</strong></div>
                        <div><small>Turb</small><br><strong>${week.avg_turbidity || '-'}</strong></div>
                        <div><small>Pakan</small><br><strong>${week.total_feed ? week.total_feed.toFixed(1) : '0'}kg</strong></div>
                    ` : `
                        <div><small>pH</small><br><span class="empty-value">--</span></div>
                        <div><small>Turb</small><br><span class="empty-value">--</span></div>
                        <div><small>Pakan</small><br><span class="empty-value">--</span></div>
                    `}
                </div>
                <div style="margin-top: 10px; font-size: 11px; color: #64748b;">
                    <i class="fas fa-calendar-alt"></i> ${week.date_range || ''}
                </div>
            </div>
        `;
    }).join('');
}

// ==================== UPDATE STAT CARDS ====================
function updateStatCards(week) {
    const weekData = weeksData.find(w => w.week == week);
    if (!weekData) return;
    
    const statPh = document.getElementById('statPh');
    const statTurb = document.getElementById('statTurb');
    const statFeed = document.getElementById('statFeed');
    const currentWeekInfo = document.getElementById('currentWeekInfo');
    const dailyWeekTitle = document.getElementById('dailyWeekTitle');
    
    if (statPh) statPh.innerHTML = weekData.avg_ph || '7.0';
    if (statTurb) statTurb.innerHTML = (weekData.avg_turbidity || '12') + ' <span style="font-size:14px;">NTU</span>';
    if (statFeed) statFeed.innerHTML = weekData.total_feed ? weekData.total_feed.toFixed(1) + ' kg' : '0 kg';
    if (currentWeekInfo) currentWeekInfo.innerHTML = `Menampilkan data ${weekData.period}`;
    if (dailyWeekTitle) dailyWeekTitle.innerHTML = weekData.period;
}

// ==================== LOAD DAILY DATA FOR WEEK ====================
async function loadDailyDataForWeek(week) {
    console.log("🔄 Load daily data untuk minggu:", week);
    
    // Update active week di timeline
    document.querySelectorAll('.week-card').forEach(card => {
        card.classList.remove('active');
        if (card.getAttribute('data-week') == week) {
            card.classList.add('active');
        }
    });
    
    try {
        const response = await fetch(`/history/week-data/${week}`);
        const result = await response.json();
        
        console.log("📊 Daily data received:", result);
        
        if (result.success) {
            renderDailyTable(result.daily_data);
            dailyDataCache[week] = result.daily_data;
        } else {
            renderEmptyDailyTable();
        }
    } catch (error) {
        console.error("Error loading daily data:", error);
        renderEmptyDailyTable();
    }
}

// ==================== RENDER DAILY TABLE ====================
function renderDailyTable(dailyData) {
    const tbody = document.getElementById('dailyTableBody');
    if (!tbody) return;
    
    if (!dailyData || dailyData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">📭 Belum ada data untuk minggu ini</div></div>';
        return;
    }
    
    tbody.innerHTML = '';
    
    for (let i = 0; i < dailyData.length; i++) {
        const day = dailyData[i];
        
        let statusClass = 'good';
        let statusText = 'Baik';
        
        if (day.status == 'Perhatian') {
            statusClass = 'warning';
            statusText = 'Perhatian';
        } else if (day.status == 'Kritis') {
            statusClass = 'danger';
            statusText = 'Kritis';
        }
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><strong>${day.date}</strong></td>
            <td>${day.day_name}</div>
            <td>${day.avg_ph}</div>
            <td>${day.avg_turbidity} <span style="font-size:14px;">NTU</span></div>
            <td>${day.total_feed ? day.total_feed.toFixed(1) : '0'} kg</div>
            <td><span class="status-badge ${statusClass}">${statusText}</span></div>
            <td>
                <button class="btn-detail" onclick="showDetail('${day.date}', '${day.day_name}')">
                    <i class="fas fa-eye"></i> Detail
                </button>
            </div>
        `;
        tbody.appendChild(row);
    }
}

function renderEmptyDailyTable() {
    const tbody = document.getElementById('dailyTableBody');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">📭 Belum ada data monitoring untuk minggu ini</div></div>';
    }
}

// ==================== SELECT WEEK ====================
window.selectWeek = function(week) {
    currentWeek = week;
    renderTimeline();
    updateStatCards(week);
    loadDailyDataForWeek(week);
};

// ==================== SHOW DETAIL MODAL (PER JAM & PAKAN) ====================
window.showDetail = async function(date, day) {
    const modal = document.getElementById('detailModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    
    if (!modal || !modalTitle || !modalBody) return;
    
    modalTitle.innerHTML = `<i class="fas fa-calendar-day"></i> Detail Monitoring - ${day}, ${date}`;
    modalBody.innerHTML = '<div class="text-center">Memuat data...</div>';
    modal.classList.add('active');
    
    try {
        // Ambil data dari server
        const response = await fetch(`/history/day-detail/${date}`);
        const result = await response.json();
        
        console.log("Detail data:", result);
        
        if (result.success) {
            let html = `
                <div class="section-title">
                    <i class="fas fa-chart-line"></i>
                    <span>Data Monitoring Per Jam</span>
                </div>
                <div style="overflow-x: auto;">
                    <table class="hourly-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>pH</th>
                                <th>Kekeruhan (NTU)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            if (result.hourly_data && result.hourly_data.length > 0) {
                result.hourly_data.forEach(hour => {
                    let statusBadge = '';
                    if (hour.status === 'Normal') {
                        statusBadge = '<span class="status-badge good">✅ Normal</span>';
                    } else if (hour.status === 'Perhatian') {
                        statusBadge = '<span class="status-badge warning">⚠️ Perhatian</span>';
                    } else {
                        statusBadge = '<span class="status-badge danger">🔴 Kritis</span>';
                    }
                    
                    html += `
                        <tr>
                            <td><i class="far fa-clock"></i> ${hour.time}</td>
                            <td><strong>${hour.ph}</strong></td>
                            <td>${hour.turbidity} NTU</div>
                            <td>${statusBadge}</div>
                        </tr>
                    `;
                });
            } else {
                html += '<tr><td colspan="4" class="text-center">Belum ada data per jam</td></tr>';
            }
            
            html += `
                        </tbody>
                    </table>
                </div>
                
                <div class="section-title">
                    <i class="fas fa-fish"></i>
                    <span>Pemberian Pakan</span>
                </div>
                <div class="modal-feed-list">
            `;
            
            if (result.feeding_data && result.feeding_data.length > 0) {
                result.feeding_data.forEach(feed => {
                    html += `
                        <div class="feed-item-modal">
                            <div class="feed-time"><i class="far fa-clock"></i> ${feed.time}</div>
                            <div class="feed-amount">${feed.amount} gram (${feed.amount_kg} kg)</div>
                            <div class="feed-note">${feed.note}</div>
                            <div class="feed-status">${feed.status_text}</div>
                        </div>
                    `;
                });
            } else {
                html += '<div class="text-center">Belum ada catatan pemberian pakan</div>';
            }
            
            html += `
                </div>
                <div class="section-title">
                    <i class="fas fa-info-circle"></i>
                    <span>Catatan</span>
                </div>
                <div class="note-box">
                    <i class="fas fa-lightbulb"></i> Data diambil dari sensor setiap 5 menit
                </div>
            `;
            
            modalBody.innerHTML = html;
        } else {
            modalBody.innerHTML = '<div class="text-center" style="color:red;">❌ Gagal memuat data detail</div>';
        }
    } catch (error) {
        console.error("Error loading detail:", error);
        modalBody.innerHTML = '<div class="text-center" style="color:red;">❌ Gagal memuat data</div>';
    }
};

// ==================== CLOSE MODAL ====================
window.closeModal = function() {
    const modal = document.getElementById('detailModal');
    if (modal) modal.classList.remove('active');
};

// ==================== CLICK OUTSIDE MODAL ====================
document.addEventListener('click', function(e) {
    const modal = document.getElementById('detailModal');
    if (e.target === modal) closeModal();
});

// ==================== INITIALIZE ====================
document.addEventListener('DOMContentLoaded', function() {
    initHistoryData();
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('footbar.utama', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Skirpsi\tambak_udang\resources\views/dashboard/history.blade.php ENDPATH**/ ?>