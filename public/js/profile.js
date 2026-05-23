// ==================== PROFILE PAGE JAVASCRIPT ====================
// Version: 2.0 - Fixed & Optimized

// ==================== DOM READY ====================
document.addEventListener('DOMContentLoaded', () => {
    console.log('Profile page loaded');
    
    // Auto hide toast
    const toast = document.getElementById('toast');
    if (toast) {
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Load realtime data
    loadRealtimeData();
    loadFeedingRecommendation();
    
    // Update setiap 5 detik
    setInterval(loadRealtimeData, 5000);
    setInterval(loadFeedingRecommendation, 10000);
    
    // Preview foto
    initFotoPreview();
});

// ==================== LOAD REALTIME DATA ====================
async function loadRealtimeData() {
    try {
        const response = await fetch('/api/realtime');
        const data = await response.json();
        
        console.log('Realtime data:', data);
        
        // Update kualitas air di card
        const phElement = document.getElementById('qualityPh');
        const phStatusElement = document.getElementById('qualityPhStatus');
        const turbElement = document.getElementById('qualityTurb');
        const turbStatusElement = document.getElementById('qualityTurbStatus');
        
        if (phElement) {
            phElement.innerText = data.ph ? data.ph.toFixed(2) : '7.0';
        }
        if (phStatusElement) {
            phStatusElement.innerText = capitalize(data.ph_status || 'normal');
            phStatusElement.className = 'quality-status ' + getStatusClass(data.ph_status);
        }
        
        if (turbElement) {
            turbElement.innerText = data.turbidity ? data.turbidity + ' NTU' : '30 NTU';
        }
        if (turbStatusElement) {
            turbStatusElement.innerText = capitalize(data.turbidity_status || 'normal');
            turbStatusElement.className = 'quality-status ' + getStatusClass(data.turbidity_status);
        }
        
        // Update summary ringkasan
        const summaryPh = document.getElementById('summaryPh');
        const summaryTurb = document.getElementById('summaryTurb');
        if (summaryPh) summaryPh.innerText = data.ph ? data.ph.toFixed(2) : '7.0';
        if (summaryTurb) summaryTurb.innerText = data.turbidity ? data.turbidity + ' NTU' : '30 NTU';
        
    } catch (error) {
        console.error('Error loading realtime data:', error);
        setDefaultRealtimeValues();
    }
}

// ==================== LOAD FEEDING RECOMMENDATION ====================
async function loadFeedingRecommendation() {
    try {
        const response = await fetch('/api/feeding/recommendation');
        const data = await response.json();
        
        if (data.success) {
            console.log('Feeding recommendation:', data.data);
            
            // Update stat card (Total Pakan per Hari)
            const statPakanElement = document.querySelector('.stat-card:last-child .stat-info h3');
            if (statPakanElement) {
                statPakanElement.innerHTML = data.data.pakan_rekomendasi_kg + ' <span>kg</span>';
            }
            
            // Update detail pakan di feeding card
            const pakanPerEkorElement = document.querySelector('.feeding-card .info-row:first-child .info-value');
            const totalPakanElement = document.querySelector('.feeding-card .info-row:last-child .info-value');
            
            if (pakanPerEkorElement) {
                pakanPerEkorElement.innerHTML = data.data.pakan_per_ekor + ' <span style="font-size:11px; color:#666;">gram/hari</span>';
            }
            if (totalPakanElement) {
                totalPakanElement.innerHTML = data.data.pakan_rekomendasi_kg + ' kg <small>(' + data.data.pakan_rekomendasi_gram.toLocaleString() + ' gram)</small>';
            }
            
            // Update atau buat jadwal pakan 3x sehari
            updateJadwalPakan(data.data.pakan_rekomendasi_gram);
        }
    } catch (error) {
        console.error('Error loading feeding recommendation:', error);
    }
}

// ==================== UPDATE JADWAL PAKAN 3X SEHARI ====================
function updateJadwalPakan(totalGram) {
    const pakanPerJadwal = Math.round(totalGram / 3);
    
    // Cari atau buat container jadwal
    let jadwalContainer = document.getElementById('jadwalPakanContainer');
    const feedingCard = document.querySelector('.feeding-card .card-body');
    
    if (!feedingCard) return;
    
    if (!jadwalContainer) {
        jadwalContainer = document.createElement('div');
        jadwalContainer.id = 'jadwalPakanContainer';
        jadwalContainer.className = 'jadwal-container';
        feedingCard.appendChild(jadwalContainer);
    }
    
    jadwalContainer.innerHTML = `
        <div class="jadwal-header">
            <span>📋 Jadwal Pakan (3x sehari)</span>
        </div>
        <div class="jadwal-item">
            <span>🌅 Pagi (06:00 - 07:00)</span>
            <span class="jadwal-value">${pakanPerJadwal.toLocaleString()} gram</span>
        </div>
        <div class="jadwal-item">
            <span>☀️ Siang (11:00 - 12:00)</span>
            <span class="jadwal-value">${pakanPerJadwal.toLocaleString()} gram</span>
        </div>
        <div class="jadwal-item">
            <span>🌙 Sore (16:00 - 17:00)</span>
            <span class="jadwal-value">${pakanPerJadwal.toLocaleString()} gram</span>
        </div>
        <div class="jadwal-item total">
            <span>📦 Total per Hari</span>
            <span class="jadwal-value">${totalGram.toLocaleString()} gram (${(totalGram/1000).toFixed(2)} kg)</span>
        </div>
    `;
}

// ==================== SET DEFAULT VALUES (SAAT ERROR) ====================
function setDefaultRealtimeValues() {
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

// ==================== MODAL FUNCTIONS ====================
function openEditModal() {
    const modal = document.getElementById('editModal');
    if (modal) modal.style.display = 'flex';
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    if (modal) modal.style.display = 'none';
}

function openBudidayaModal() {
    const modal = document.getElementById('budidayaModal');
    if (modal) modal.style.display = 'flex';
}

function closeBudidayaModal() {
    const modal = document.getElementById('budidayaModal');
    if (modal) modal.style.display = 'none';
}

function kalibrasi(param) {
    showToast(`Fitur kalibrasi untuk ${param} akan segera tersedia`, 'info');
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

// ==================== TOAST FUNCTION ====================
function showToast(message, type = 'success') {
    let toast = document.getElementById('toast');
    if (toast) toast.remove();
    
    toast = document.createElement('div');
    toast.id = 'toast';
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <span>${type === 'success' ? '✅' : (type === 'error' ? '❌' : 'ℹ️')}</span>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ==================== HELPER FUNCTIONS ====================
function capitalize(str) {
    if (!str) return 'Normal';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function getStatusClass(status) {
    const statusMap = {
        'baik': 'normal',
        'good': 'normal',
        'normal': 'normal',
        'aman': 'normal',
        'peringatan': 'warning',
        'warning': 'warning',
        'bahaya': 'danger',
        'danger': 'danger'
    };
    return statusMap[status?.toLowerCase()] || 'normal';
}

// ==================== PREVIEW FOTO ====================
function initFotoPreview() {
    const fotoInput = document.querySelector('input[name="foto_tambak"]');
    const previewImg = document.getElementById('previewFoto');
    
    if (fotoInput && previewImg) {
        fotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    previewImg.src = event.target.result;
                    previewImg.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                previewImg.style.display = 'none';
            }
        });
    }
}

// ==================== CLOSE MODAL ON OUTSIDE CLICK ====================
window.onclick = function(event) {
    const modals = ['editModal', 'budidayaModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
}
// ==================== KALIBRASI SENSOR ====================

// Modal kalibrasi pH
// ==================== KALIBRASI SENSOR ====================
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
// ==================== KALIBRASI SENSOR ====================

// Pastikan CSRF token ada
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

// ==================== KALIBRASI SENSOR ====================

function kalibrasiPH() {
    console.log("Kalibrasi pH diklik");
    
    // Ambil nilai pH saat ini
    fetch('/api/realtime')
        .then(response => response.json())
        .then(data => {
            const currentPh = data.ph;
            const desiredPh = 7.0;
            
            if (confirm(`📊 Kalibrasi pH\n\nNilai pH saat ini: ${currentPh}\nNilai yang diinginkan: ${desiredPh}\n\nLanjutkan kalibrasi?`)) {
                fetch('/api/calibrate/ph', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        desired_value: desiredPh,
                        current_value: currentPh
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showToast(result.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast(result.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Gagal kalibrasi pH', 'error');
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Gagal mengambil data sensor', 'error');
        });
}

function kalibrasiTurbidity() {
    console.log("Kalibrasi Turbidity diklik");
    
    // Ambil nilai turbidity saat ini
    fetch('/api/realtime')
        .then(response => response.json())
        .then(data => {
            const currentTurb = data.turbidity;
            const desiredTurb = 30;
            
            if (confirm(`📊 Kalibrasi Turbidity\n\nNilai Turbidity saat ini: ${currentTurb} NTU\nNilai yang diinginkan: ${desiredTurb} NTU\n\nLanjutkan kalibrasi?`)) {
                fetch('/api/calibrate/turbidity', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        desired_value: desiredTurb,
                        current_value: currentTurb
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showToast(result.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast(result.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Gagal kalibrasi turbidity', 'error');
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Gagal mengambil data sensor', 'error');
        });
}
// Cek status kalibrasi
function checkCalibrationStatus() {
    fetch('/api/calibration/status')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const calibStatus = document.getElementById('calibrationStatus');
                if (calibStatus) {
                    if (data.is_calibrated) {
                        calibStatus.innerHTML = `✅ Terkalibrasi (Offset: pH ${data.ph_offset})`;
                        calibStatus.style.color = '#10b981';
                    } else {
                        calibStatus.innerHTML = `⚠️ Belum dikalibrasi (pH: ${data.current_ph}, NTU: ${data.current_turbidity})`;
                        calibStatus.style.color = '#f59e0b';
                    }
                }
                
                // Notifikasi jika noise tinggi
                if (data.noise_warning) {
                    showToast(data.noise_warning, 'warning');
                }
            }
        })
        .catch(error => console.error('Error:', error));
}

// Kalibrasi Turbidity
function kalibrasiTurbidity() {
    fetch('/api/realtime')
        .then(response => response.json())
        .then(data => {
            const currentTurb = data.turbidity;
            const desiredTurb = 30; // Nilai default yang diinginkan
            
            const message = `📊 Kalibrasi Turbidity\n\n` +
                `Nilai Turbidity saat ini: ${currentTurb} NTU\n` +
                `Nilai yang diinginkan: ${desiredTurb} NTU\n\n` +
                `⚠️ Kalibrasi akan menyesuaikan pembacaan sensor.\n` +
                `Lanjutkan kalibrasi?`;
            
            if (confirm(message)) {
                fetch('/api/calibrate/turbidity', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        desired_value: desiredTurb,
                        current_value: currentTurb
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showToast(result.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast(result.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Gagal kalibrasi turbidity', 'error');
                });
            }
        })
        .catch(error => console.error('Error:', error));
}

// Reset kalibrasi
function resetCalibration(type) {
    const message = `⚠️ Reset Kalibrasi ${type.toUpperCase()}\n\n` +
        `Tindakan ini akan mengembalikan nilai kalibrasi ke default.\n` +
        `Lanjutkan?`;
    
    if (confirm(message)) {
        fetch('/api/calibration/reset', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ type: type })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showToast(result.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(result.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Gagal reset kalibrasi', 'error');
        });
    }
}

// Cek status kalibrasi dan noice
function checkCalibrationStatus() {
    fetch('/api/calibration/status')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update tampilan status kalibrasi
                const calibStatus = document.getElementById('calibrationStatus');
                if (calibStatus) {
                    if (data.is_calibrated) {
                        calibStatus.innerHTML = `✅ Terkalibrasi (${new Date(data.last_calibration).toLocaleDateString()})`;
                        calibStatus.style.color = '#10b981';
                    } else {
                        calibStatus.innerHTML = `⚠️ Belum dikalibrasi`;
                        calibStatus.style.color = '#f59e0b';
                    }
                }
                
                // Tampilkan notifikasi jika noice tinggi
                if (data.noise_warning) {
                    showToast(data.noise_warning, 'warning');
                    
                    // Tambahkan class warning pada card kualitas air
                    const qualityCard = document.querySelector('.quality-card');
                    if (qualityCard && data.noise_level === 'tinggi') {
                        qualityCard.style.border = '2px solid #ef4444';
                        qualityCard.style.boxShadow = '0 0 15px rgba(239, 68, 68, 0.3)';
                    }
                }
            }
        })
        .catch(error => console.error('Error:', error));
}

// Panggil cek status saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    checkCalibrationStatus();
    setInterval(checkCalibrationStatus, 60000); // Cek setiap 1 menit
});