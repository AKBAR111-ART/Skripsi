// ============================================
// MONITORING PAGE - FINAL VERSION
// ============================================

const API = {
    REALTIME: '/sensor/realtime',
    STATS: '/api/monitoring/stats',
    HISTORY: '/api/monitoring/history',
    FEEDING: '/api/monitoring/feeding',
    AVG_DATA: '/api/monitoring/avg-data',
    MONITORING_DATA: '/monitoring/data'
};

let allMonitoringData = [];
let currentDisplayData = [];
let monitoringUpdateInterval = null;
let lastMonitoringCount = 0;
let charts = {};

// ==================== GAUGE FUNCTIONS (SAMA DENGAN HOME) ====================
function createGauge(id, color, maxValue) {
    const canvas = document.getElementById(id);
    if (!canvas) return;
    if (charts[id]) charts[id].destroy();
    
    const ctx = canvas.getContext('2d');
    charts[id] = new Chart(ctx, {
        type: 'doughnut',
        data: { 
            datasets: [{ 
                data: [0, maxValue], 
                backgroundColor: [color, '#e5e7eb'], 
                borderWidth: 0, 
                borderRadius: 10 
            }] 
        },
        options: { 
            rotation: -90, 
            circumference: 180, 
            cutout: '70%', 
            responsive: true, 
            maintainAspectRatio: true,
            plugins: { 
                legend: { display: false }, 
                tooltip: { enabled: false } 
            } 
        }
    });
}

function updateGauge(id, value, maxValue) {
    if (!charts[id]) return;
    let val = Math.min(Math.max(value, 0), maxValue);
    charts[id].data.datasets[0].data = [val, maxValue - val];
    charts[id].update();
}

function updatePH(ph, status) {
    const phText = document.getElementById('phCenterVal');
    const phStatus = document.getElementById('phGaugeBadge');
    
    let phValue = parseFloat(ph || 0);
    if (isNaN(phValue)) phValue = 7.0;
    if (phValue < 0) phValue = 0;
    if (phValue > 14) phValue = 14;
    
    if (phText) {
        phText.innerHTML = phValue.toFixed(2);
    }
    
    if (phStatus) {
        let statusText = 'Normal';
        let statusClass = 'badge-success';
        
        if (status === 'bahaya' || status === 'danger') {
            statusText = 'Bahaya';
            statusClass = 'badge-danger';
        } else if (status === 'peringatan' || status === 'warning') {
            statusText = 'Peringatan';
            statusClass = 'badge-warning';
        }
        
        phStatus.innerHTML = statusText;
        phStatus.className = `gauge-badge ${statusClass}`;
    }
    
    if (charts['phGauge']) {
        updateGauge('phGauge', phValue, 14);
    }
}

function updateTurbidity(turbidity, status) {
    const turbText = document.getElementById('turbCenterVal');
    const turbStatus = document.getElementById('turbGaugeBadge');
    
    let turbValue = parseFloat(turbidity || 0);
    if (isNaN(turbValue)) turbValue = 0;
    if (turbValue < 0) turbValue = 0;
    if (turbValue > 1000) turbValue = 1000;
    
    if (turbText) {
        turbText.innerHTML = Math.round(turbValue) + ' NTU';
    }
    
    if (turbStatus) {
        let statusText = 'Normal';
        let statusClass = 'badge-success';
        
        if (status === 'bahaya' || status === 'danger') {
            statusText = 'Bahaya';
            statusClass = 'badge-danger';
        } else if (status === 'peringatan' || status === 'warning') {
            statusText = 'Peringatan';
            statusClass = 'badge-warning';
        }
        
        turbStatus.innerHTML = statusText;
        turbStatus.className = `gauge-badge ${statusClass}`;
    }
    
    if (charts['turbGauge']) {
        updateGauge('turbGauge', turbValue, 1000);
    }
}

// ==================== LOAD REALTIME ====================
async function loadRealtime() {
    try {
        const response = await fetch(API.REALTIME);
        
        if (!response.ok) {
            console.error("API response error:", response.status);
            return;
        }
        
        const data = await response.json();
        console.log("Realtime data received:", data);
        
        if (!data || typeof data.ph === 'undefined') {
            console.error("Invalid data format:", data);
            return;
        }
        
        updatePH(data.ph, data.ph_status || 'normal');
        updateTurbidity(data.turbidity, data.turbidity_status || 'normal');
        
        const phStatusCard = document.getElementById('phStatusDisplay');
        const turbStatusCard = document.getElementById('turbidityStatusDisplay');
        
        if (phStatusCard) {
            let statusText = 'Normal';
            if (data.ph_status === 'bahaya') statusText = 'Bahaya';
            else if (data.ph_status === 'peringatan') statusText = 'Peringatan';
            phStatusCard.innerHTML = statusText;
        }
        
        if (turbStatusCard) {
            let statusText = 'Normal';
            if (data.turbidity_status === 'bahaya') statusText = 'Bahaya';
            else if (data.turbidity_status === 'peringatan') statusText = 'Peringatan';
            turbStatusCard.innerHTML = statusText;
        }
        
    } catch (err) {
        console.error("Realtime error:", err.message);
    }
}

// ==================== FETCH STATS ====================
async function fetchStats() {
    try {
        const response = await fetch(API.STATS);
        const data = await response.json();
        if (data.success) {
            const totalFeedDisplay = document.getElementById('totalFeedDisplay');
            const recommendFeedValue = document.getElementById('recommendFeedValue');
            const biomassaDisplay = document.getElementById('biomassaDisplay');
            const jumlahPemberianDisplay = document.getElementById('jumlahPemberianDisplay');
            
            if (totalFeedDisplay) totalFeedDisplay.innerHTML = (data.total_feed || 0) + ' <span>gram</span>';
            if (recommendFeedValue) recommendFeedValue.innerHTML = (data.total_feed || 0);
            if (biomassaDisplay) biomassaDisplay.innerHTML = (data.biomassa || 0) + ' <span>kg</span>';
            if (jumlahPemberianDisplay) jumlahPemberianDisplay.innerHTML = data.jumlah_pemberian || 0;
            
            updateProgressBar(data.total_feed || 0);
            updateRecommendationFromStats(data.ph_status, data.turbidity_status);
        }
    } catch (error) { console.error('Error fetching stats:', error); }
}

function updateRecommendationFromStats(phStatus, turbStatus) {
    let reason = '', alertClass = 'warning';
    
    if (turbStatus === 'Tinggi' || turbStatus === 'bahaya') {
        reason = '⚠️ AIR SANGAT KERUH - Pakan dikurangi 50%';
        alertClass = 'danger';
    } else if (turbStatus === 'Sedang' || turbStatus === 'peringatan') {
        reason = '⚠️ AIR KERUH - Pakan dikurangi 30%';
        alertClass = 'warning';
    } else if (phStatus === 'Rendah' || phStatus === 'Tinggi' || phStatus === 'peringatan') {
        reason = '⚠️ pH TIDAK NORMAL - Pakan dikurangi 20%';
        alertClass = 'warning';
    } else {
        reason = '✅ Kondisi air normal, pakan sesuai target';
        alertClass = 'success';
    }
    
    const alertReason = document.getElementById('alertReason');
    const alertBox = document.getElementById('alertWarningBox');
    if (alertReason) alertReason.innerHTML = reason;
    if (alertBox) alertBox.className = `alert-warning-box ${alertClass}`;
}

// ==================== RENDER MONITORING TABLE ====================
function renderMonitoringTable(data) {
    const tbody = document.getElementById('monitoringTableBody');
    if (!tbody) {
        console.error("Element monitoringTableBody tidak ditemukan!");
        return;
    }
    
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">📭 Belum ada data untuk tanggal ini</div></div>';
        return;
    }
    
    tbody.innerHTML = '';
    
    for (let i = 0; i < data.length; i++) {
        const item = data[i];
        
        let statusClass = 'success';
        let statusText = 'Normal';
        
        if (item.status === 'Kritis') {
            statusClass = 'danger';
            statusText = 'Kritis';
        } else if (item.status === 'Sedang') {
            statusClass = 'warning';
            statusText = 'Sedang';
        }
        
        const waktu = item.waktu || '-';
        const ph = item.ph || '-';
        const kekeruhan = item.kekeruhan ? parseFloat(item.kekeruhan).toFixed(2) : '-';
        
        const row = tbody.insertRow();
        row.innerHTML = `
            <td style="padding: 8px 12px; border-bottom: 1px solid #eef2f6;"><strong>${waktu}</strong></td>
            <td style="padding: 8px 12px; border-bottom: 1px solid #eef2f6;">${ph}</td>
            <td style="padding: 8px 12px; border-bottom: 1px solid #eef2f6;">${kekeruhan} <span style="color:#94a3b8;">NTU</span></td>
            <td style="padding: 8px 12px; border-bottom: 1px solid #eef2f6;"><span class="status-badge ${statusClass}">${statusText}</span></td>
        `;
    }
    
    console.log("✅ Tabel dirender, jumlah baris:", data.length);
}

// ==================== LOAD MONITORING DATA PER TANGGAL ====================
async function loadMonitoringData() {
    const datePicker = document.getElementById('monitoringDate');
    const date = datePicker ? datePicker.value : new Date().toISOString().slice(0,10);
    
    console.log("🔄 Mengambil data untuk tanggal:", date);
    
    try {
        const response = await fetch(`/monitoring/data?date=${date}`);
        const result = await response.json();
        
        console.log("📊 Data diterima, jumlah record:", result.data ? result.data.length : 0);
        
        if (result.success && result.data) {
            allMonitoringData = result.data;
            renderMonitoringTable(allMonitoringData);
            
            const infoElement = document.getElementById('monitoringInfo');
            if (infoElement && result.stats) {
                const formattedDate = new Date(date).toLocaleDateString('id-ID');
                infoElement.innerHTML = `
                    <i class="fas fa-info-circle"></i> 
                    📅 ${formattedDate} | 
                    📊 Rata-rata pH: ${result.stats.avg_ph || '-'} | 
                    💧 Rata-rata NTU: ${result.stats.avg_turbidity || '-'} |
                    📈 Status: ${result.stats.status || 'Normal'}
                `;
            }
        } else {
            renderMonitoringTable([]);
        }
    } catch (error) {
        console.error("❌ Error:", error);
        const tbody = document.getElementById('monitoringTableBody');
        if (tbody) {
            tbody.innerHTML = `<td><td colspan="4" style="text-align:center; color:red;">❌ Gagal memuat data: ${error.message}</td></tr>`;
        }
    }
}

// ==================== FETCH FEEDING TABLE ====================
async function fetchFeedingTable() {
    try {
        const response = await fetch(API.FEEDING);
        const data = await response.json();
        
        if (data.success) {
            if (data.schedule && Array.isArray(data.schedule) && data.schedule.length > 0) {
                renderFeedingTable(data.schedule);
            } else {
                renderEmptyFeedingTable('📭 Belum ada catatan pemberian pakan hari ini.');
            }
            
            const totalFeedDisplay = document.getElementById('totalFeedDisplay');
            const recommendFeedValue = document.getElementById('recommendFeedValue');
            const jumlahPemberianDisplay = document.getElementById('jumlahPemberianDisplay');
            
            if (totalFeedDisplay) totalFeedDisplay.innerHTML = (data.total_gram || 0) + ' <span>gram</span>';
            if (recommendFeedValue) recommendFeedValue.innerHTML = (data.total_gram || 0);
            if (jumlahPemberianDisplay) jumlahPemberianDisplay.innerHTML = data.jumlah_pemberian || 0;
            
            updateProgressBar(data.total_gram || 0);
        } else {
            renderEmptyFeedingTable('📭 Belum ada catatan pemberian pakan hari ini.');
        }
    } catch (error) { 
        console.error('Error fetching feeding:', error);
        renderEmptyFeedingTable('❌ Gagal memuat data pakan');
    }
}

function renderFeedingTable(schedule) {
    const tbody = document.getElementById('feedTableBody');
    if (!tbody) return;
    
    if (!schedule || schedule.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding:30px;">📭 Belum ada catatan pemberian pakan hari ini</td></tr>';
        return;
    }
    
    tbody.innerHTML = '';
    for (let i = 0; i < schedule.length; i++) {
        const item = schedule[i];
        const isCompleted = (item.status === 'Sudah' || item.status === 'sudah' || item.record_id !== null);
        const statusClass = isCompleted ? 'success' : 'warning';
        const statusText = isCompleted ? '✓ Sudah' : '⌛ Belum';
        const waktuDisplay = item.waktu ? `(${item.waktu})` : '';
        const keteranganDisplay = item.keterangan ? `<br><small style="color:#94a3b8; font-size:11px;">${item.keterangan}</small>` : '';
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.icon || '🍽️'} ${item.pukul || '-'} ${waktuDisplay}${keteranganDisplay}</td>
            <td><strong>${item.jumlah || 0}</strong> <span style="color:#94a3b8;">gram</span></td>
            <td><span class="status-badge ${statusClass}">${statusText}</span></td>
        `;
        tbody.appendChild(row);
    }
}

function renderEmptyFeedingTable(message) {
    const tbody = document.getElementById('feedTableBody');
    if (tbody) {
        tbody.innerHTML = `<tr><td colspan="3" style="text-align:center; padding:30px;">${message}</td></tr>`;
    }
}

function updateProgressBar(totalGram) {
    const targetPakan = 500;
    const percent = Math.min(100, Math.round((totalGram / targetPakan) * 100));
    const progressFill = document.querySelector('.progress-fill');
    const progressNote = document.querySelector('.progress-note');
    if (progressFill) progressFill.style.width = percent + '%';
    if (progressNote) progressNote.innerHTML = `Realisasi: ${totalGram} gram (${percent}%)`;
}

// ==================== SEARCH FUNCTION ====================
function initSearch() {
    const searchInput = document.getElementById('searchMonitoring');
    if (!searchInput) return;
    
    searchInput.addEventListener('input', (e) => {
        const keyword = e.target.value.trim().toLowerCase();
        
        if (keyword === '') {
            currentDisplayData = allMonitoringData;
            renderMonitoringTable(currentDisplayData);
            return;
        }
        
        const filtered = allMonitoringData.filter(item => {
            if (!item.waktu) return false;
            return item.waktu.includes(keyword);
        });
        
        if (filtered.length > 0) {
            currentDisplayData = filtered;
            renderMonitoringTable(currentDisplayData);
            showToast(`Ditemukan ${filtered.length} data untuk jam "${keyword}"`, 'info');
        } else {
            const tbody = document.getElementById('monitoringTableBody');
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">🔍 Tidak ada data untuk jam "${keyword}"</td></tr>`;
            }
        }
    });
}

// ==================== REFRESH ALL DATA ====================
async function refreshAllData() {
    const refreshBtn = document.getElementById('refreshDataBtn');
    const icon = refreshBtn?.querySelector('i');
    if (icon) { 
        icon.style.transform = 'rotate(360deg)'; 
        icon.style.transition = 'transform 0.5s'; 
        setTimeout(() => { 
            if(icon) icon.style.transform = 'rotate(0deg)'; 
        }, 500); 
    }
    await Promise.all([fetchStats(), loadMonitoringData(), fetchFeedingTable()]);
    
    const lastUpdateElement = document.getElementById('lastUpdateInfo');
    if (lastUpdateElement) {
        lastUpdateElement.innerHTML = `<i class="fas fa-clock"></i> Terakhir update: ${new Date().toLocaleTimeString('id-ID')} | Auto update setiap 30 detik`;
    }
    
    showToast('Data berhasil diperbarui', 'success');
}

function showToast(message, type) {
    let toast = document.getElementById('toast');
    if (toast) toast.remove();
    
    toast = document.createElement('div');
    toast.id = 'toast';
    toast.className = `toast ${type}`;
    toast.innerHTML = `<div class="toast-content">${type === 'success' ? '✅' : (type === 'info' ? 'ℹ️' : '❌')} ${message}</div>`;
    document.body.appendChild(toast);
    toast.style.display = 'flex';
    
    setTimeout(() => {
        if (toast) toast.remove();
    }, 3000);
}

function updateHeroDateTime() {
    const dateElement = document.getElementById('heroDate');
    const timeElement = document.getElementById('heroTime');
    const now = new Date();
    
    if (dateElement) {
        dateElement.innerHTML = now.toLocaleDateString('id-ID', { 
            day: 'numeric', 
            month: 'long', 
            year: 'numeric' 
        });
    }
    if (timeElement) {
        timeElement.innerHTML = now.toLocaleTimeString('id-ID', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    }
}

// ==================== INITIALIZATION ====================
document.addEventListener('DOMContentLoaded', async function() {
    console.log('🚀 Monitoring page loaded');
    
    createGauge('phGauge', '#667eea', 14);
    createGauge('turbGauge', '#f59e0b', 1000);
    
    await refreshAllData();
    await loadRealtime();
    
    setInterval(loadRealtime, 3000);
    
    const refreshBtn = document.getElementById('refreshDataBtn');
    if (refreshBtn) refreshBtn.addEventListener('click', refreshAllData);
    
    const datePicker = document.getElementById('monitoringDate');
    if (datePicker) {
        datePicker.addEventListener('change', function() {
            loadMonitoringData();
        });
    }
    
    initSearch();
    updateHeroDateTime();
    setInterval(updateHeroDateTime, 60000);
    setInterval(loadMonitoringData, 30000);
    
    console.log('✅ Monitoring realtime aktif');
});

window.addEventListener('beforeunload', function() { 
    if (monitoringUpdateInterval) clearInterval(monitoringUpdateInterval);
});