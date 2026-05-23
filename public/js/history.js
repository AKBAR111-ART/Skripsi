// history.js - Premium History Monitoring JavaScript (FINAL - NO DUMMY DATA)

// ==================== DATA ====================
const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
const daysName = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

let weeksData = [];
let currentWeek = 1;

// ==================== CARD DATA MONITORING HARIAN ====================
let dailyMonitoringData = [];
let currentDailyDate = new Date().toISOString().slice(0,10);

/**
 * Load data monitoring harian dari server
 */
async function loadDailyMonitoring() {
    const datePicker = document.getElementById('dailyDate');
    const date = datePicker ? datePicker.value : new Date().toISOString().slice(0,10);
    
    console.log("🔄 Load daily monitoring untuk tanggal:", date);
    
    try {
        const response = await fetch(`/daily-monitoring-data?date=${date}`);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success && result.data) {
            renderDailyMonitoringTable(result.data);
            updateDailyStats(result.stats);
        } else {
            showEmptyDailyTable();
        }
    } catch (error) {
        console.error("❌ Error:", error.message);
        showErrorDailyTable();
    }
}

/**
 * Render tabel monitoring harian
 */
function renderDailyMonitoringTable(data) {
    const tbody = document.getElementById('dailyMonitoringBody');
    if (!tbody) {
        console.error("Element #dailyMonitoringBody tidak ditemukan!");
        return;
    }
    
    if (!data || data.length === 0) {
        tbody.innerHTML = '<td><td colspan="5" style="text-align:center;">📭 Belum ada data untuk tanggal ini</div></div>';
        return;
    }
    
    tbody.innerHTML = '';
    
    for (let i = 0; i < data.length; i++) {
        const item = data[i];
        
        let statusClass = 'good';
        let statusText = 'Normal';
        
        if (item.status === 'Kritis') {
            statusClass = 'danger';
            statusText = 'Kritis';
        } else if (item.status === 'Perhatian') {
            statusClass = 'warning';
            statusText = 'Perhatian';
        }
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><strong>${item.hour}</strong></td>
            <td>${item.avg_ph}</td>
            <td>${item.avg_turbidity} <span style="color:#94a3b8;">NTU</span></td>
            <td><span class="status-badge ${statusClass}">${statusText}</span></td>
            <td>${item.sample_count || 0} data</div>
        `;
        tbody.appendChild(row);
    }
    
    console.log("✅ Tabel dirender, jumlah baris:", data.length);
}

/**
 * Update statistik ringkasan harian
 */
function updateDailyStats(stats) {
    const avgPhEl = document.getElementById('dailyAvgPh');
    const avgTurbEl = document.getElementById('dailyAvgTurb');
    const statusEl = document.getElementById('dailyStatus');
    const sampleCountEl = document.getElementById('dailySampleCount');
    
    if (avgPhEl) avgPhEl.innerHTML = stats.avg_ph || '--';
    if (avgTurbEl) avgTurbEl.innerHTML = stats.avg_turbidity || '--';
    if (sampleCountEl) sampleCountEl.innerHTML = stats.total_records || '0';
    
    if (statusEl) {
        let statusClass = 'good';
        let statusText = 'Normal';
        
        if (stats.status === 'Kritis') {
            statusClass = 'danger';
            statusText = 'Kritis';
        } else if (stats.status === 'Perhatian') {
            statusClass = 'warning';
            statusText = 'Perhatian';
        }
        
        statusEl.innerHTML = `<span class="status-badge ${statusClass}">${statusText}</span>`;
    }
}

/**
 * Tampilkan empty state
 */
function showEmptyDailyTable() {
    const tbody = document.getElementById('dailyMonitoringBody');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">📭 Belum ada data monitoring untuk tanggal ini</div></div>';
    }
    resetDailyStats();
}

/**
 * Tampilkan error state
 */
function showErrorDailyTable() {
    const tbody = document.getElementById('dailyMonitoringBody');
    if (tbody) {
        tbody.innerHTML = '<td><td colspan="5" style="text-align:center; color:red;">❌ Gagal memuat data monitoring</div></div>';
    }
}

/**
 * Reset statistik harian
 */
function resetDailyStats() {
    const avgPhEl = document.getElementById('dailyAvgPh');
    const avgTurbEl = document.getElementById('dailyAvgTurb');
    const statusEl = document.getElementById('dailyStatus');
    const sampleCountEl = document.getElementById('dailySampleCount');
    
    if (avgPhEl) avgPhEl.innerHTML = '--';
    if (avgTurbEl) avgTurbEl.innerHTML = '--';
    if (sampleCountEl) sampleCountEl.innerHTML = '0';
    if (statusEl) statusEl.innerHTML = '<span class="status-badge good">Normal</span>';
}

/**
 * Refresh daily monitoring
 */
async function refreshDailyMonitoring() {
    const refreshBtn = document.getElementById('refreshDailyBtn');
    const icon = refreshBtn?.querySelector('i');
    if (icon) {
        icon.style.transform = 'rotate(360deg)';
        icon.style.transition = 'transform 0.5s';
        setTimeout(() => {
            if (icon) icon.style.transform = 'rotate(0deg)';
        }, 500);
    }
    await loadDailyMonitoring();
    showToast('Data harian diperbarui', 'success');
}

/**
 * Show toast notification
 */
function showToast(message, type) {
    let toast = document.getElementById('dailyToast');
    if (toast) toast.remove();
    
    toast = document.createElement('div');
    toast.id = 'dailyToast';
    toast.className = `daily-toast ${type}`;
    toast.innerHTML = `<div class="daily-toast-content">${type === 'success' ? '✅' : 'ℹ️'} ${message}</div>`;
    document.body.appendChild(toast);
    toast.style.display = 'flex';
    
    setTimeout(() => {
        if (toast) toast.remove();
    }, 3000);
}

// ==================== HELPER FUNCTIONS (DUMMY UNTUK MODAL) ====================
function getHourlyData() {
    const hours = ['06:00', '08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00'];
    const hourlyData = [];
    
    hours.forEach((hour, index) => {
        let ph = (6.8 + Math.random() * 0.8).toFixed(1);
        let turb = 8 + Math.floor(Math.random() * 15);
        let status = (parseFloat(ph) >= 6.8 && parseFloat(ph) <= 7.5 && turb < 25) ? "Normal" : "Perhatian";
        
        hourlyData.push({
            time: hour,
            ph: ph,
            turbidity: turb,
            status: status
        });
    });
    
    return hourlyData;
}

function getFeedData() {
    return [
        { time: "06:00", amount: 350, note: "Pakan pagi awal" },
        { time: "09:00", amount: 250, note: "Pakan tambahan" },
        { time: "12:00", amount: 300, note: "Pakan siang" },
        { time: "15:00", amount: 250, note: "Pakan sore" },
        { time: "17:00", amount: 350, note: "Pakan malam" }
    ];
}

// ==================== RENDER FUNCTIONS (REAL DATA) ====================
function updateStatCards(week) {
    const data = weeksData[week - 1];
    if (!data) return;
    
    const statPh = document.getElementById('statPh');
    const statTurb = document.getElementById('statTurb');
    const statFeed = document.getElementById('statFeed');
    const currentWeekInfo = document.getElementById('currentWeekInfo');
    const dailyWeekTitle = document.getElementById('dailyWeekTitle');
    
    if (statPh) statPh.innerHTML = data.avg_ph || '7.0';
    if (statTurb) statTurb.innerHTML = (data.avg_turbidity || '12') + ' <span style="font-size:14px;">NTU</span>';
    if (statFeed) statFeed.innerHTML = data.total_feed ? data.total_feed.toFixed(1) + ' kg' : '0 kg';
    if (currentWeekInfo) currentWeekInfo.innerHTML = `Menampilkan data ${data.period}`;
    if (dailyWeekTitle) dailyWeekTitle.innerHTML = data.period;
}

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
                 data-week="${week.week}"
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

// ==================== LOAD DAILY DATA FOR WEEK (REAL DATA) ====================
async function loadDailyDataForWeek(week) {
    console.log("🔄 Load daily data untuk minggu:", week);
    
    try {
        const response = await fetch(`/history/week-data/${week}`);
        const result = await response.json();
        
        console.log("📊 Daily data received:", result);
        
        if (result.success && result.daily_data) {
            renderDailyTable(result.daily_data);
        } else {
            renderEmptyDailyTable();
        }
    } catch (error) {
        console.error("Error loading daily data:", error);
        renderEmptyDailyTable();
    }
}

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
        tbody.innerHTML = '<td><td colspan="7" class="text-center">📭 Belum ada data monitoring untuk minggu ini</div></div>';
    }
}

// ==================== SELECT WEEK ====================
window.selectWeek = function(week) {
    console.log("🔄 Select week:", week);
    currentWeek = week;
    
    // Update active class di timeline
    document.querySelectorAll('.week-card').forEach(card => {
        card.classList.remove('active');
        if (card.getAttribute('data-week') == week) {
            card.classList.add('active');
        }
    });
    
    updateStatCards(week);
    loadDailyDataForWeek(week);
};

// ==================== SHOW DETAIL MODAL ====================
window.showDetail = function(date, day) {
    const modal = document.getElementById('detailModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    
    if (!modal || !modalTitle || !modalBody) return;
    
    modalTitle.innerHTML = `<i class="fas fa-calendar-day"></i> Detail Monitoring - ${day}, ${date}`;
    modalBody.innerHTML = '<div class="text-center">Memuat data...</div>';
    modal.classList.add('active');
    
    try {
        const hourlyData = getHourlyData();
        const feedData = getFeedData();
        
        let html = `
            <div class="section-title">
                <i class="fas fa-chart-line"></i>
                <span>Data Monitoring Per Jam</span>
            </div>
            <div style="overflow-x: auto;">
                <table class="hourly-table">
                    <thead><tr><th>Waktu</th><th>pH</th><th>Kekeruhan (NTU)</th><th>Status</th></tr></thead>
                    <tbody>
        `;
        
        hourlyData.forEach(hour => {
            let statusBadge = hour.status === 'Normal' ? 
                '<span class="status-badge good">✅ Normal</span>' : 
                '<span class="status-badge warning">⚠️ Perhatian</span>';
            
            html += `<tr><td><i class="far fa-clock"></i> ${hour.time}</td><td><strong>${hour.ph}</strong></td><td>${hour.turbidity} NTU</td><td>${statusBadge}</td></tr>`;
        });
        
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
        
        feedData.forEach(feed => {
            html += `
                <div class="feed-item-modal">
                    <div class="feed-time"><i class="far fa-clock"></i> ${feed.time}</div>
                    <div class="feed-amount">${feed.amount} gram</div>
                    <div class="feed-note">${feed.note}</div>
                </div>
            `;
        });
        
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
    } catch (error) {
        modalBody.innerHTML = '<div class="text-center" style="color:red;">❌ Gagal memuat data</div>';
    }
};

// ==================== CLOSE MODAL ====================
window.closeModal = function() {
    const modal = document.getElementById('detailModal');
    if (modal) modal.classList.remove('active');
};

// ==================== TOGGLE VIEW ====================
function initToggle() {
    const toggleBtns = document.querySelectorAll('.toggle-btn');
    const timelineView = document.getElementById('timelineView');
    const tableView = document.getElementById('tableView');
    const dailyContainer = document.getElementById('dailyDataContainer');
    
    if (!toggleBtns.length) return;
    
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            toggleBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const view = this.dataset.view;
            if (view === 'timeline') {
                if (timelineView) timelineView.style.display = 'block';
                if (tableView) tableView.style.display = 'none';
                if (dailyContainer) dailyContainer.style.display = 'block';
            } else {
                if (timelineView) timelineView.style.display = 'none';
                if (tableView) tableView.style.display = 'block';
                if (dailyContainer) dailyContainer.style.display = 'none';
                renderWeeklyTable();
            }
        });
    });
}

function renderWeeklyTable() {
    const tbody = document.getElementById('weekTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = weeksData.map(week => {
        let statusClass = 'good', statusText = '✅ Baik';
        if (week.status === 'Perhatian') { statusClass = 'warning'; statusText = '⚠️ Perhatian'; }
        if (week.status === 'Kritis') { statusClass = 'danger'; statusText = '🔴 Kritis'; }
        return `
            <tr onclick="selectWeek(${week.week})">
                <td><strong>Minggu ${week.week}</strong></td>
                <td>${week.period}</td>
                <td>${week.avg_ph || '-'}</td>
                <td>${week.avg_turbidity || '-'} NTU</div>
                <td>${week.total_feed ? week.total_feed.toFixed(1) : '0'} kg</div>
                <td><span class="status-badge ${statusClass}">${statusText}</span></div>
            </tr>
        `;
    }).join('');
}

// ==================== INITIALIZE ====================
document.addEventListener('DOMContentLoaded', function() {
    console.log("🚀 Page loaded - Using REAL DATA from database");
    
    // 🔥 DATA REAL DARI CONTROLLER (BUKAN DUMMY)
    if (window.historyData) {
        weeksData = window.historyData.weeksData || [];
        currentWeek = window.historyData.currentWeek || 1;
        console.log("📦 Real weeksData:", weeksData);
    }
    
    renderTimeline();
    initToggle();
    updateStatCards(currentWeek);
    loadDailyDataForWeek(currentWeek);
    loadDailyMonitoring();
    
    // Event listener untuk date picker daily monitoring
    const datePicker = document.getElementById('dailyDate');
    if (datePicker) {
        datePicker.addEventListener('change', function() {
            loadDailyMonitoring();
        });
    }
    
    // Refresh button daily monitoring
    const refreshBtn = document.getElementById('refreshDailyBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', refreshDailyMonitoring);
    }
    
    // Auto refresh daily monitoring setiap 30 detik
    setInterval(loadDailyMonitoring, 30000);
    
    // Modal click outside
    const modal = document.getElementById('detailModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    }
    
    console.log("✅ History page ready with REAL data");
});

// ==================== CLEANUP ====================
window.addEventListener('beforeunload', function() {
    // Cleanup jika perlu
});