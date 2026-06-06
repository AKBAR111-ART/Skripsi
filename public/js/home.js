// ==================== HOME.JS - FINAL DENGAN CUACA ====================
let charts = {};

// ==================== PH SMOOTHING VARIABLES ====================
let lastStablePH = 7.0;
let phValueHistory = [];
let smoothPH = 7.0;

// ==================== FUNGSI CUACA & PRODUCTION (TAMBAHAN BARU) ====================
async function loadCuaca() {
    try {
        const response = await fetch('/api/production-variables');
        const result = await response.json();
        
        if (result.success && result.data) {
            const data = result.data;
            
            // Update card cuaca
            const cuacaText = document.getElementById('cuacaText');
            const suhuText = document.getElementById('suhuText');
            const hujanText = document.getElementById('hujanText');
            const cuacaFeedInfo = document.getElementById('cuacaFeedInfo');
            
            if (cuacaText) cuacaText.innerHTML = data.cuaca || 'Cerah';
            if (suhuText) suhuText.innerHTML = `Suhu: ${data.suhu_lingkungan || '--'}°C`;
            if (hujanText) hujanText.innerHTML = `${data.intensitas_hujan || 0} <span>mm</span>`;
            if (cuacaFeedInfo) {
                let cuacaIcon = data.cuaca === 'Hujan' ? '☔' : (data.cuaca === 'Berawan' ? '☁️' : '☀️');
                cuacaFeedInfo.innerHTML = `${cuacaIcon} ${data.cuaca} | ${data.suhu_lingkungan || '--'}°C`;
            }
            
            console.log("✅ Data cuaca diupdate:", data.cuaca, data.suhu_lingkungan);
        }
    } catch (error) {
        console.error("❌ Error loading cuaca:", error);
    }
}

async function loadProductionData() {
    try {
        const response = await fetch('/api/production-variables');
        const result = await response.json();
        
        if (result.success && result.data) {
            const data = result.data;
            
            // Update data tambahan
            const umurText = document.getElementById('umurText');
            const umurMinggu = document.getElementById('umurMinggu');
            const beratRata = document.getElementById('beratRata');
            const biomassa = document.getElementById('biomassa');
            const targetPanen = document.getElementById('targetPanen');
            const targetSize = document.getElementById('targetSize');
            const topFeed = document.getElementById('topFeed');
            
            if (umurText) umurText.innerHTML = `${data.umur_minggu || 0} <span>Minggu</span>`;
            if (umurMinggu) umurMinggu.innerHTML = `${data.umur_minggu || 0} Minggu`;
            if (beratRata) beratRata.innerHTML = `${data.avg_weight || 0} gram`;
            if (biomassa) biomassa.innerHTML = `${data.biomassa_kg || 0} kg`;
            if (targetPanen) targetPanen.innerHTML = `${data.target_panen_kg || 0} kg`;
            if (targetSize) targetSize.innerHTML = `${data.target_size_gram || 0} gram`;
            if (topFeed && data.total_pakan_harian_kg !== undefined) {
                topFeed.innerHTML = `${data.total_pakan_harian_kg} <span>kg</span>`;
            }
            
            console.log("✅ Data produksi diupdate:", data);
        }
    } catch (error) {
        console.error("❌ Error loading production data:", error);
    }
}

// Init All Charts
function initAllCharts() {
    console.log("🚀 Init All Charts dipanggil");
    
    if (document.getElementById('chartPh')) {
        createLineChart('chartPh', '#667eea', 'pH');
        console.log("✅ chartPh dibuat");
    }
    if (document.getElementById('chartTurb')) {
        createLineChart('chartTurb', '#f59e0b', 'Turbidity (NTU)');
        console.log("✅ chartTurb dibuat");
    }
    if (document.getElementById('chartFeed')) {
        createBarChart('chartFeed', '#10b981', 'Pakan (gram)');
        console.log("✅ chartFeed dibuat");
    }
    if (document.getElementById('phGauge')) {
        createGauge('phGauge', '#667eea');
        console.log("✅ phGauge dibuat");
    }
    if (document.getElementById('turbGauge')) {
        createGauge('turbGauge', '#f59e0b');
        console.log("✅ turbGauge dibuat");
    }
    
    console.log("charts object:", charts);
}

// Create Line Chart Premium
function createLineChart(id, color, label) {
    const canvas = document.getElementById(id);
    if (!canvas) return;
    if (charts[id]) charts[id].destroy();
    
    const ctx = canvas.getContext('2d');
    charts[id] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: label,
                data: [],
                borderColor: color,
                backgroundColor: color + '15',
                borderWidth: 2.5,
                pointRadius: 4,
                pointBackgroundColor: color,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: color,
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 2,
                tension: 0.3,
                fill: true,
                shadowOffsetX: 2,
                shadowOffsetY: 2,
                shadowBlur: 4,
                shadowColor: color + '40'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: { size: 11, family: "'Inter', sans-serif", weight: '500' },
                        color: '#4a5568',
                        usePointStyle: true,
                        boxWidth: 8,
                        padding: 15
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#e2e8f0',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    cornerRadius: 12,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            let value = context.raw;
                            return `${label}: ${value.toFixed(2)}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false,
                        lineWidth: 1
                    },
                    ticks: {
                        font: { size: 10, family: "'Inter', sans-serif" },
                        color: '#6b7280',
                        stepSize: 2,
                        callback: function(value) {
                            return value.toFixed(1);
                        }
                    },
                    title: {
                        display: true,
                        text: label,
                        font: { size: 10, family: "'Inter', sans-serif", weight: '500' },
                        color: '#6b7280'
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        font: { size: 9, family: "'Inter', sans-serif" },
                        color: '#6b7280',
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            },
            elements: {
                line: {
                    borderJoin: 'round',
                    borderCap: 'round'
                }
            },
            interaction: {
                mode: 'index',
                intersect: false
            },
            hover: {
                mode: 'nearest',
                intersect: true
            }
        }
    });
}

// Create Bar Chart Premium
function createBarChart(id, color, label) {
    const canvas = document.getElementById(id);
    if (!canvas) return;
    if (charts[id]) charts[id].destroy();
    
    const ctx = canvas.getContext('2d');
    charts[id] = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
            datasets: [{
                label: label,
                data: [0, 0, 0, 0, 0, 0, 0],
                backgroundColor: color,
                borderRadius: 12
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Gram' }
                }
            }
        }
    });
}

// Load Weekly Feed Chart
async function loadWeeklyFeedChart() {
    try {
        const response = await fetch('/api/sensor/weekly-feed');
        const result = await response.json();
        
        console.log("Weekly feed data:", result);
        
        if (result.success && charts['chartFeed']) {
            charts['chartFeed'].data.datasets[0].data = result.data;
            charts['chartFeed'].update();
        }
    } catch (error) {
        console.error("Error loading weekly feed chart:", error);
    }
}

// Update Line Chart
function updateLineChart(chartId, newValue) {
    if (!charts[chartId]) {
        if (chartId === 'chartPh') createLineChart('chartPh', '#667eea', 'pH');
        if (chartId === 'chartTurb') createLineChart('chartTurb', '#f59e0b', 'Turbidity (NTU)');
        return;
    }
    
    const now = new Date();
    const timeLabel = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}:${now.getSeconds().toString().padStart(2, '0')}`;
    
    charts[chartId].data.labels.push(timeLabel);
    charts[chartId].data.datasets[0].data.push(newValue);
    
    if (charts[chartId].data.labels.length > 15) {
        charts[chartId].data.labels.shift();
        charts[chartId].data.datasets[0].data.shift();
    }
    
    charts[chartId].update({
        duration: 300,
        easing: 'easeInOutQuart'
    });
}

// Update Bar Chart
function updateBarChart(value) {
    if (!charts['chartFeed']) return;
    const chart = charts['chartFeed'];
    chart.data.datasets[0].data.push(value);
    if (chart.data.datasets[0].data.length > 7) {
        chart.data.datasets[0].data.shift();
    }
    chart.update({
        duration: 300,
        easing: 'easeInOutQuart'
    });
}

// Gauge
function createGauge(id, color) {
    const canvas = document.getElementById(id);
    if (!canvas) return;
    if (charts[id]) charts[id].destroy();
    
    let maxValue = 100;
    if (id === 'phGauge') maxValue = 14;
    if (id === 'turbGauge') maxValue = 100;
    
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
            plugins: { 
                legend: { display: false }, 
                tooltip: { enabled: false } 
            } 
        }
    });
}

function updateGauge(id, value, maxValue) {
    console.log("📊 updateGauge dipanggil:", id, "value:", value, "max:", maxValue);
    
    if (!charts[id]) {
        console.error("❌ Chart dengan id", id, "tidak ditemukan!");
        return;
    }
    
    let val = Math.min(Math.max(value, 0), maxValue);
    console.log("📊 Nilai setelah constrain:", val);
    
    charts[id].data.datasets[0].data = [val, maxValue - val];
    charts[id].update();
    console.log("✅ Gauge", id, "diupdate");
}

// ==================== UPDATE PH - FINAL VERSION ====================
let phMovingBuffer = [];

function updatePH(ph, status) {
    const phText = document.getElementById('phText');
    const phStatus = document.getElementById('phStatus');
    
    let phValue = parseFloat(ph);
    if (isNaN(phValue)) phValue = 7.0;
    phValue = Math.min(Math.max(phValue, 0), 14);
    
    // Moving average 3 data
    phMovingBuffer.push(phValue);
    if (phMovingBuffer.length > 3) phMovingBuffer.shift();
    
    let sum = 0;
    for (let i = 0; i < phMovingBuffer.length; i++) sum += phMovingBuffer[i];
    let smoothValue = sum / phMovingBuffer.length;
    
    // Update teks
    if (phText) phText.innerHTML = smoothValue.toFixed(2);
    
    // Update status badge
    if (phStatus) {
        const statusText = status || 'normal';
        phStatus.innerHTML = capitalize(statusText);
        phStatus.className = `status-badge ${getStatusClass(statusText)}`;
    }
    
    // Update gauge
    if (charts['phGauge']) {
        updateGauge('phGauge', smoothValue, 14);
    }
}

// ==================== UPDATE TURBIDITY ====================
function updateTurbidity(turbidity, status) {
    const turbText = document.getElementById('turbText');
    const turbStatus = document.getElementById('turbStatus');
    
    let turbValue = parseFloat(turbidity || 0);
    
    // Validasi range
    if (turbValue < 0) turbValue = 0;
    if (turbValue > 1000) turbValue = 1000;
    
    // Update teks
    if (turbText) {
        turbText.innerHTML = Math.round(turbValue) + ' NTU';
    }
    
    // Update status badge
    if (turbStatus) {
        const statusText = status || 'normal';
        turbStatus.innerHTML = capitalize(statusText);
        turbStatus.className = `status-badge ${getStatusClass(statusText)}`;
    }
    
    // Update gauge (max 100 NTU untuk tampilan)
    let displayValue = Math.min(turbValue, 100);
    if (charts['turbGauge']) {
        updateGauge('turbGauge', displayValue, 100);
    }
}

function updateTopBar(data) {
    const topWater = document.getElementById('topWater');
    if (topWater && data) {
        topWater.innerHTML = `pH ${data.ph || 0} | NTU ${data.turbidity || 0}`;
    }
}

// ==================== LOAD REALTIME ====================
async function loadRealtime() {
    try {
        const response = await fetch('/sensor/realtime');
        
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
        updateTopBar(data);
        updateLineChart('chartPh', parseFloat(data.ph));
        updateLineChart('chartTurb', parseFloat(data.turbidity));
        updateAlertBox(data);
        updateStatusKondisiTambak();
        
    } catch (err) {
        console.error("Realtime error:", err.message);
    }
}

function updateAlertBox(data) {
    const alertBox = document.getElementById('alertBox');
    if (!alertBox) return;
    const isBahaya = data.ph_status === 'bahaya' || data.turbidity_status === 'bahaya';
    const isPeringatan = data.ph_status === 'peringatan' || data.turbidity_status === 'peringatan';
    if (isBahaya) {
        alertBox.className = 'alert-premium danger';
        alertBox.innerHTML = '<i class="fas fa-skull-crosswalk"></i> ⚠️ KONDISI TAMBAK BAHAYA! Segera lakukan tindakan!';
    } else if (isPeringatan) {
        alertBox.className = 'alert-premium warning';
        alertBox.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ⚠️ PERINGATAN: Kualitas air mulai tidak stabil. Harap periksa!';
    } else {
        alertBox.className = 'alert-premium normal';
        alertBox.innerHTML = '<i class="fas fa-check-circle"></i> ✅ Kondisi tambak stabil. Kualitas air dalam batas normal.';
    }
}

async function updateStatusKondisiTambak() {
    try {
        const response = await fetch('/sensor/realtime');
        const data = await response.json();
        
        let statusText = '';
        let statusColor = '';
        let statusDetail = '';
        
        const isBahaya = data.ph_status === 'bahaya' || data.turbidity_status === 'bahaya';
        const isPeringatan = data.ph_status === 'peringatan' || data.turbidity_status === 'peringatan';
        
        if (isBahaya) {
            statusText = 'BAHAYA';
            statusColor = '#dc2626';
            statusDetail = `⚠️ pH: ${data.ph} (${data.ph_status}) | NTU: ${data.turbidity} (${data.turbidity_status})`;
        } else if (isPeringatan) {
            statusText = 'PERINGATAN';
            statusColor = '#f59e0b';
            statusDetail = `⚠️ pH: ${data.ph} (${data.ph_status}) | NTU: ${data.turbidity} (${data.turbidity_status})`;
        } else {
            statusText = 'AMAN';
            statusColor = '#10b981';
            statusDetail = `✅ pH: ${data.ph} (optimal) | NTU: ${data.turbidity} (normal)`;
        }
        
        const statusElement = document.getElementById('statusKondisiTambak');
        const statusDetailElement = document.getElementById('statusDetail');
        
        if (statusElement) {
            statusElement.innerHTML = statusText;
            statusElement.style.color = statusColor;
        }
        if (statusDetailElement) {
            statusDetailElement.innerHTML = statusDetail;
        }
        
    } catch (error) {
        console.error("Error update status kondisi tambak:", error);
    }
}

// ==================== LOAD ESTIMASI PAKAN ====================
// ==================== LOAD ESTIMASI PAKAN (DIPERBAIKI) ====================
async function loadEstimasiPakan() {
    console.log("🔄 Loading estimasi pakan...");
    
    try {
        const response = await fetch('/api/sensor/getFeedingRecommendation');
        const data = await response.json();
        
        console.log("Response API Estimasi Pakan:", data);
        
        const feedValue = document.getElementById('feedValue');
        const feedBox = document.querySelector('.feed-box');
        
        if (data.success && data.data) {
            // Ambil nilai rekomendasi (prioritaskan rekomendasi_kg)
            let rekomendasi = data.data.rekomendasi_kg || data.data.pakan_rekomendasi_kg || 0;
            let pakanDasar = data.data.pakan_dasar_kg || 0;
            let faktorAir = data.data.faktor_air || 1;
            let faktorCuaca = data.data.faktor_cuaca || 1;
            let statusAir = data.data.status_air || 'aman';
            let statusCuaca = data.data.status_cuaca || 'cerah';
            let keterangan = data.data.keterangan || '';
            
            if (feedValue) {
                feedValue.innerHTML = rekomendasi + ' kg';
            }
            
            // Update warna sesuai status
            if (feedValue) {
                if (statusAir === 'bahaya') {
                    feedValue.style.color = '#dc2626';
                    feedValue.style.fontSize = '36px';
                } else if (statusAir === 'peringatan' || statusCuaca.includes('hujan')) {
                    feedValue.style.color = '#f59e0b';
                } else {
                    feedValue.style.color = '#10b981';
                }
            }
            
            // Update tooltip / detail di feed box
            if (feedBox && !document.getElementById('feedDetail')) {
                const detailDiv = document.createElement('div');
                detailDiv.id = 'feedDetail';
                detailDiv.style.cssText = 'font-size: 10px; color: rgba(255,255,255,0.7); margin-top: 8px;';
                feedBox.appendChild(detailDiv);
            }
            
            const detailDiv = document.getElementById('feedDetail');
            if (detailDiv) {
                let detailText = `📊 Dasar: ${pakanDasar} kg | Air: ${Math.round(faktorAir*100)}% | Cuaca: ${Math.round(faktorCuaca*100)}%`;
                detailDiv.innerHTML = detailText;
            }
            
            // Update alert reason jika ada
            const alertReason = document.getElementById('alertReason');
            if (alertReason && keterangan) {
                alertReason.innerHTML = keterangan;
            }
            
            console.log("✅ Estimasi pakan diupdate:", rekomendasi, "kg");
            
        } else {
            if (feedValue) feedValue.innerHTML = '0.5 kg';
            console.log("Data tidak lengkap:", data);
        }
    } catch (error) {
        console.error("Error loading estimasi:", error);
        const feedValue = document.getElementById('feedValue');
        if (feedValue) feedValue.innerHTML = '0.5 kg';
    }
}

// ==================== FUNGSI LAINNYA ====================
async function updateAkumulasiPakanHariIni() {
    console.log("Update akumulasi pakan - endpoint perlu dibuat");
}

async function loadFeedingHistory() {
    console.log("Load feeding history - endpoint perlu dibuat");
}

function loadLatestProfileData() {
    fetch('/api/profile/latest')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const populasiElement = document.querySelector('.stat-card:last-child .stat-info h3');
                if (populasiElement) {
                    populasiElement.innerHTML = data.populasi.toLocaleString() + ' <span>ekor</span>';
                }
                const beratElement = document.getElementById('beratRata');
                if (beratElement) beratElement.innerHTML = data.avg_weight + ' gram';
                const biomassaElement = document.getElementById('biomassa');
                if (biomassaElement) biomassaElement.innerHTML = data.biomassa_kg + ' kg';
                const umurElement = document.getElementById('umurMinggu');
                if (umurElement) umurElement.innerHTML = data.umur_minggu + ' Minggu';
            }
        })
        .catch(error => console.error('Error loading profile data:', error));
}

// Kirim Pakan Otomatis
async function kirimPakan() {
    const feedValueElement = document.getElementById('feedValue');
    let pakanText = feedValueElement ? feedValueElement.innerText : '0';
    let pakanKg = parseFloat(pakanText);
    let pakanGram = pakanKg * 1000;
    
    if (isNaN(pakanGram) || pakanGram <= 0) {
        showToast("❌ Jumlah pakan tidak valid", "error");
        return;
    }
    
    const jam = new Date().getHours();
    let jadwal = 'sore';
    if (jam >= 5 && jam < 11) jadwal = 'pagi';
    else if (jam >= 11 && jam < 15) jadwal = 'siang';
    
    const btn = event?.target;
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
    }
    
    try {
        const response = await fetch('/api/sensor/send-feed-command', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                pakan: pakanGram,
                jadwal: jadwal,
                sumber: 'estimasi'
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, "success");
            setTimeout(() => {
                loadTodayFeeding();
                loadEstimasiPakan();
            }, 500);
        } else {
            showToast(result.message || "❌ Gagal mengirim pakan", "error");
        }
    } catch (error) {
        console.error("Error:", error);
        showToast("❌ Gagal mengirim pakan: " + error.message, "error");
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Pakan';
        }
    }
}

// Kirim Pakan Manual (Modal)
async function sendEdit() {
    const input = document.getElementById('manualPakan');
    
    if (!input) {
        console.error("Element dengan id 'manualPakan' tidak ditemukan!");
        showToast("❌ Error: Input tidak ditemukan", "error");
        return;
    }
    
    let rawValue = input.value.trim();
    console.log("Raw value:", rawValue);
    
    if (rawValue === "") {
        showToast("❌ Jumlah pakan tidak boleh kosong", "error");
        return;
    }
    
    let pakanGram = parseFloat(rawValue);
    console.log("Parsed value:", pakanGram);
    
    if (isNaN(pakanGram)) {
        showToast("❌ Masukkan angka yang valid", "error");
        return;
    }
    
    if (pakanGram <= 0) {
        showToast("❌ Jumlah pakan minimal 1 gram", "error");
        return;
    }
    
    if (pakanGram > 10000) {
        showToast("❌ Jumlah pakan maksimal 10.000 gram (10 kg)", "error");
        return;
    }
    
    const jam = new Date().getHours();
    let jadwal = 'sore';
    if (jam >= 5 && jam < 11) jadwal = 'pagi';
    else if (jam >= 11 && jam < 15) jadwal = 'siang';
    
    const btn = document.querySelector('#editModal .btn-primary');
    const originalText = btn ? btn.innerHTML : 'Kirim';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
    }
    
    try {
        const response = await fetch('/api/sensor/send-feed-command', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                pakan: pakanGram,
                jadwal: jadwal,
                sumber: 'manual'
            })
        });
        
        const result = await response.json();
        console.log("Response:", result);
        
        if (result.success) {
            showToast(result.message, "success");
            input.value = '';
            closeEdit();
            
            setTimeout(() => {
                if (typeof loadTodayFeeding === 'function') loadTodayFeeding();
                if (typeof loadEstimasiPakan === 'function') loadEstimasiPakan();
            }, 500);
        } else {
            showToast(result.message || "❌ Gagal mengirim pakan", "error");
        }
    } catch (error) {
        console.error("Error:", error);
        showToast("❌ Gagal mengirim pakan: " + error.message, "error");
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
}

// Modal
function openEdit() { document.getElementById('editModal').classList.add('show'); }
function closeEdit() { document.getElementById('editModal').classList.remove('show'); }

// Helper
function capitalize(str) {
    if (!str) return 'Normal';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function getStatusClass(status) {
    const map = { 
        'baik': 'baik',
        'aman': 'baik', 
        'normal': 'baik',
        'peringatan': 'peringatan',
        'warning': 'peringatan',
        'bahaya': 'bahaya',
        'danger': 'bahaya'
    };
    return map[status?.toLowerCase()] || 'baik';
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<div class="toast-content"><span>${type === 'success' ? '✅' : '❌'}</span><span>${message}</span></div>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// ==================== INIT ====================
document.addEventListener("DOMContentLoaded", function() {
    setTimeout(() => { 
        initAllCharts(); 
        loadRealtime(); 
        loadCuaca();
        loadProductionData();
    }, 100);
    
    loadEstimasiPakan();
    loadWeeklyFeedChart(); 
    updateAkumulasiPakanHariIni();
    loadFeedingHistory();
    loadLatestProfileData();
    updateStatusKondisiTambak();
    
    setInterval(loadRealtime, 3000);
    setInterval(loadCuaca, 30000);
    setInterval(loadProductionData, 30000);
    setInterval(loadEstimasiPakan, 10000);
    setInterval(loadWeeklyFeedChart, 60000);
    setInterval(updateAkumulasiPakanHariIni, 10000);
    setInterval(loadFeedingHistory, 30000);
    setInterval(loadLatestProfileData, 30000);
    setInterval(updateStatusKondisiTambak, 3000);
});

window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target === modal) closeEdit();
};