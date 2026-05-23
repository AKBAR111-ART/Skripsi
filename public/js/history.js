// history.js - Premium History Monitoring JavaScript (FINAL)

let weeksData = [];
let currentWeek = 1;

// ==================== INITIALIZE ====================
function initHistoryData() {
    if (window.historyData) {
        weeksData = window.historyData.weeksData || [];
        currentWeek = window.historyData.currentWeek || 1;
        
        // Set biomassa
        const statBiomassa = document.getElementById('statBiomassa');
        if (statBiomassa && window.historyData.biomassaKg) {
            statBiomassa.innerHTML = window.historyData.biomassaKg.toFixed(2) + ' <span style="font-size:14px;">kg</span>';
        }
        
        renderTimeline();
        updateStatCards(currentWeek);
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

// ==================== UPDATE STAT CARDS ====================
function updateStatCards(week) {
    const weekData = weeksData.find(w => w.week == week);
    if (!weekData) return;
    
    const statPh = document.getElementById('statPh');
    const statTurb = document.getElementById('statTurb');
    const statFeed = document.getElementById('statFeed');
    const currentWeekInfo = document.getElementById('currentWeekInfo');
    const dailyWeekTitle = document.getElementById('dailyWeekTitle');
    
    // Update stat pH
    if (statPh) statPh.innerHTML = weekData.avg_ph || '7.0';
    
    // Update stat Kekeruhan
    if (statTurb) statTurb.innerHTML = (weekData.avg_turbidity || '12') + ' <span style="font-size:14px;">NTU</span>';
    
    // 🔥 UPDATE STAT TOTAL PAKAN - dari data mingguan
    if (statFeed) {
        const totalFeed = weekData.total_feed && weekData.total_feed !== '--' ? weekData.total_feed.toFixed(1) : '0';
        statFeed.innerHTML = totalFeed + ' <span style="font-size:14px;">kg</span>';
    }
    
    // Update info minggu
    if (currentWeekInfo) currentWeekInfo.innerHTML = `Menampilkan data ${weekData.period}`;
    if (dailyWeekTitle) dailyWeekTitle.innerHTML = weekData.period;
}

// ==================== LOAD DAILY DATA FOR WEEK ====================
async function loadDailyDataForWeek(week) {
    console.log("🔄 Load daily data untuk minggu:", week);
    
    document.querySelectorAll('.week-card').forEach(card => {
        card.classList.remove('active');
        if (card.getAttribute('data-week') == week) {
            card.classList.add('active');
        }
    });
    
    try {
        const response = await fetch(`/history/week-data/${week}`);
        const result = await response.json();
        
        console.log("📊 Response:", result);
        
        if (result.success && result.daily_data) {
            renderDailyTable(result.daily_data);
            
            // 🔥 UPDATE CARD TOTAL PAKAN dari API response
            const statFeed = document.getElementById('statFeed');
            if (statFeed && result.total_feed_week !== undefined) {
                statFeed.innerHTML = result.total_feed_week.toFixed(1) + ' <span style="font-size:14px;">kg</span>';
            }
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
        tbody.innerHTML = '<td><td colspan="7" class="text-center">📭 Belum ada data monitoring untuk minggu ini</div></div>';
    }
}

// ==================== SELECT WEEK ====================
window.selectWeek = function(week) {
    console.log("🔄 Select week:", week);
    currentWeek = week;
    renderTimeline();
    updateStatCards(week);
    loadDailyDataForWeek(week);
};

// ==================== SHOW DETAIL MODAL ====================
window.showDetail = async function(date, day) {
    const modal = document.getElementById('detailModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    
    if (!modal || !modalTitle || !modalBody) return;
    
    modalTitle.innerHTML = `<i class="fas fa-calendar-day"></i> Detail Monitoring - ${day}, ${date}`;
    modalBody.innerHTML = '<div class="text-center">Memuat data...</div>';
    modal.classList.add('active');
    
    try {
        const response = await fetch(`/history/day-detail/${date}`);
        const result = await response.json();
        
        if (result.success) {
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
            
            result.hourly_data.forEach(hour => {
                let statusBadge = hour.status === 'Normal' ? '<span class="status-badge good">✅ Normal</span>' : 
                                 (hour.status === 'Perhatian' ? '<span class="status-badge warning">⚠️ Perhatian</span>' : '<span class="status-badge danger">🔴 Kritis</span>');
                html += `<tr><td><i class="far fa-clock"></i> ${hour.time}</td><td><strong>${hour.ph}</strong></td><td>${hour.turbidity} NTU</td><td>${statusBadge}</td></tr>`;
            });
            
            html += `</tbody></table></div>
                <div class="section-title"><i class="fas fa-fish"></i><span>Pemberian Pakan</span></div>
                <div class="modal-feed-list">`;
            
            result.feeding_data.forEach(feed => {
                html += `<div class="feed-item-modal">
                            <div class="feed-time"><i class="far fa-clock"></i> ${feed.time}</div>
                            <div class="feed-amount">${feed.amount} gram (${feed.amount_kg} kg)</div>
                            <div class="feed-note">${feed.note}</div>
                            <div class="feed-status">${feed.status_text}</div>
                         </div>`;
            });
            
            html += `</div></div>`;
            modalBody.innerHTML = html;
        } else {
            modalBody.innerHTML = '<div class="text-center" style="color:red;">❌ Gagal memuat data</div>';
        }
    } catch (error) {
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