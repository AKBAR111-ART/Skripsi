// ==================== PENGATURAN.JS - FINAL (DIPERBAIKI & DIRAPI) ====================

let editModal = null;
let penjagaContainer = null;
let waContainer = null;
let waktuContainer = null;
let tanggalInput = null;
let templatePesan = null;

// ==================== TOAST QUEUE ====================
let toastQueue = [];
let isShowingToast = false;

function showToast(message, type = "success", duration = 3000) {
    toastQueue.push({ message, type, duration });
    if (!isShowingToast) processToastQueue();
}

function processToastQueue() {
    if (toastQueue.length === 0) { isShowingToast = false; return; }
    isShowingToast = true;
    const { message, type, duration } = toastQueue.shift();
    
    const toast = document.createElement('div');
    toast.className = `toast-premium ${type}`;
    let icon = type === 'error' ? '❌' : (type === 'warning' ? '⚠️' : '✅');
    let title = type === 'error' ? 'Gagal!' : (type === 'warning' ? 'Peringatan!' : 'Berhasil!');
    
    toast.innerHTML = `<div class="toast-icon">${icon}</div><div class="toast-content"><p class="toast-title">${title}</p><p class="toast-message">${message}</p></div><button class="toast-close">&times;</button>`;
    document.body.appendChild(toast);
    
    const removeToast = () => {
        if (toast && toast.parentNode) {
            toast.classList.add('hiding');
            setTimeout(() => { if (toast && toast.parentNode) toast.remove(); processToastQueue(); }, 300);
        } else { processToastQueue(); }
    };
    
    const timeoutId = setTimeout(removeToast, duration);
    const closeBtn = toast.querySelector('.toast-close');
    if (closeBtn) closeBtn.onclick = () => { clearTimeout(timeoutId); removeToast(); };
    toast.dataset.timeoutId = timeoutId;
}
window.showToast = showToast;

// ==================== CLOSE MODAL ====================
window.closeEdit = function () { if (editModal) editModal.classList.remove("show"); };

// ==================== UPDATE BAHAYA DISPLAY ====================
function updateBahayaDisplay() {
    const phLow = document.getElementById("ph_danger_low")?.value || 6.5;
    const phHigh = document.getElementById("ph_danger_high")?.value || 9.0;
    const turLow = document.getElementById("tur_danger_low")?.value || 10;
    const turHigh = document.getElementById("tur_danger_high")?.value || 70;
    if (document.getElementById("ph_danger_low_display")) document.getElementById("ph_danger_low_display").innerText = phLow;
    if (document.getElementById("ph_danger_high_display")) document.getElementById("ph_danger_high_display").innerText = phHigh;
    if (document.getElementById("tur_danger_low_display")) document.getElementById("tur_danger_low_display").innerText = turLow;
    if (document.getElementById("tur_danger_high_display")) document.getElementById("tur_danger_high_display").innerText = turHigh;
}

// ==================== UPDATE PREVIEW ====================
function updatePreview() {
    const penjaga = [...document.querySelectorAll(".penjagaInput")].map(e => e.value).filter(Boolean);
    const wa = [...document.querySelectorAll(".waInput")].map(e => e.value).filter(Boolean);
    const waktu = [...document.querySelectorAll(".waktuInput")].map(e => e.value).filter(Boolean);
    if (document.getElementById("penjagaNow")) document.getElementById("penjagaNow").innerText = penjaga.join(", ") || "-";
    if (document.getElementById("waNow")) document.getElementById("waNow").innerText = wa.join(", ") || "-";
    if (document.getElementById("waktuNow")) document.getElementById("waktuNow").innerText = waktu.join(", ") || "-";
    if (tanggalInput && document.getElementById("tanggalNow")) document.getElementById("tanggalNow").innerText = tanggalInput.value || "-";
    
    // Update total jadwal di preview
    const totalJadwalSpan = document.getElementById('totalJadwalNow');
    if (totalJadwalSpan) {
        const jadwalItems = document.querySelectorAll('.jadwal-item');
        totalJadwalSpan.innerHTML = jadwalItems.length;
    }
}

// ==================== CREATE INPUT ====================
function createInput(type) {
    const wrapper = document.createElement("div");
    wrapper.style.display = "flex";
    wrapper.style.gap = "10px";
    wrapper.style.marginBottom = "10px";
    const input = document.createElement("input");
    input.className = `${type}Input form-control-premium`;
    input.name = `pengingat[${type}][]`;
    if (type === "waktu") input.type = "time";
    else if (type === "wa") input.type = "text", input.placeholder = "628xxxxxxxxxx";
    else input.type = "text", input.placeholder = "Nama penjaga";
    input.addEventListener("input", updatePreview);
    const btn = document.createElement("button");
    btn.type = "button";
    btn.innerHTML = "✖";
    btn.style.background = "#ffebeb";
    btn.style.border = "none";
    btn.style.padding = "0 15px";
    btn.style.borderRadius = "10px";
    btn.style.cursor = "pointer";
    btn.onclick = () => { wrapper.remove(); updatePreview(); checkEmptyData(); };
    wrapper.appendChild(input);
    wrapper.appendChild(btn);
    return wrapper;
}

// ==================== INIT ADD BUTTONS ====================
function initAddButtons() {
    document.getElementById("addPenjaga")?.addEventListener("click", () => { if (penjagaContainer) penjagaContainer.appendChild(createInput("penjaga")); });
    document.getElementById("addWa")?.addEventListener("click", () => { if (waContainer) waContainer.appendChild(createInput("wa")); });
    document.getElementById("addWaktu")?.addEventListener("click", () => { if (waktuContainer) waktuContainer.appendChild(createInput("waktu")); });
}

// ==================== LOAD EXISTING DATA ====================
function loadExistingData() {
    if (!window.pengaturanData) return;
    let penjaga = [], wa = [], waktu = [];
    try { 
        penjaga = JSON.parse(window.pengaturanData.penjaga || "[]"); 
        wa = JSON.parse(window.pengaturanData.nomor_wa || "[]"); 
        waktu = JSON.parse(window.pengaturanData.waktu || "[]"); 
    } catch(e) { console.error("Error parsing JSON:", e); }
    
    penjaga.forEach(v => { const el = createInput("penjaga"); el.querySelector("input").value = v; if (penjagaContainer) penjagaContainer.appendChild(el); });
    wa.forEach(v => { const el = createInput("wa"); el.querySelector("input").value = v; if (waContainer) waContainer.appendChild(el); });
    waktu.forEach(v => { const el = createInput("waktu"); el.querySelector("input").value = v; if (waktuContainer) waktuContainer.appendChild(el); });
    
    if (window.pengaturanData.tanggal && tanggalInput) tanggalInput.value = window.pengaturanData.tanggal;
    if (templatePesan) templatePesan.value = window.pengaturanData.template_pesan || "";
    updatePreview();
}

// ==================== SAVE RULE ====================
function initSaveRule() {
    const saveRuleBtn = document.getElementById("saveRuleBtn");
    if (!saveRuleBtn) return;
    const newSaveRuleBtn = saveRuleBtn.cloneNode(true);
    saveRuleBtn.parentNode.replaceChild(newSaveRuleBtn, saveRuleBtn);
    newSaveRuleBtn.addEventListener("click", async () => {
        try {
            const payload = {
                ph_min_good: parseFloat(document.getElementById("ph_baik_min")?.value),
                ph_max_good: parseFloat(document.getElementById("ph_baik_max")?.value),
                ph_min_warning: parseFloat(document.getElementById("ph_warn_min")?.value),
                ph_max_warning: parseFloat(document.getElementById("ph_warn_max")?.value),
                ph_min_warning_high: parseFloat(document.getElementById("ph_warn_min_high")?.value),
                ph_max_warning_high: parseFloat(document.getElementById("ph_warn_max_high")?.value),
                ph_danger_low: parseFloat(document.getElementById("ph_danger_low")?.value),
                ph_danger_high: parseFloat(document.getElementById("ph_danger_high")?.value),
                turbidity_min_good: parseFloat(document.getElementById("tur_baik_min")?.value),
                turbidity_max_good: parseFloat(document.getElementById("tur_baik_max")?.value),
                turbidity_min_warning: parseFloat(document.getElementById("tur_warn_min")?.value),
                turbidity_max_warning: parseFloat(document.getElementById("tur_warn_max")?.value),
                turbidity_danger_low: parseFloat(document.getElementById("tur_danger_low")?.value),
                turbidity_danger_high: parseFloat(document.getElementById("tur_danger_high")?.value)
            };
            const res = await fetch("/pengaturan/rule", { 
                method: "POST", 
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content }, 
                body: JSON.stringify(payload) 
            });
            const data = await res.json();
            if (!res.ok) { showToast(data.message || "Gagal", "error"); return; }
            showToast("Rule berhasil disimpan", "success");
            
            // Update display
            if (document.getElementById("ph_baik_text")) document.getElementById("ph_baik_text").innerText = `${payload.ph_min_good} - ${payload.ph_max_good}`;
            if (document.getElementById("ph_warn_text")) document.getElementById("ph_warn_text").innerHTML = `${payload.ph_min_warning} - ${payload.ph_max_warning}<br><small>atau ${payload.ph_min_warning_high} - ${payload.ph_max_warning_high}</small>`;
            if (document.getElementById("ph_bahaya_text")) document.getElementById("ph_bahaya_text").innerHTML = `< ${payload.ph_danger_low} atau > ${payload.ph_danger_high}`;
            if (document.getElementById("tur_baik_text")) document.getElementById("tur_baik_text").innerText = `${payload.turbidity_min_good} - ${payload.turbidity_max_good}`;
            if (document.getElementById("tur_warn_text")) document.getElementById("tur_warn_text").innerText = `${payload.turbidity_min_warning} - ${payload.turbidity_max_warning}`;
            if (document.getElementById("tur_bahaya_text")) document.getElementById("tur_bahaya_text").innerHTML = `< ${payload.turbidity_danger_low} atau > ${payload.turbidity_danger_high}`;
            
            window.rule_sensor = payload;
            window.closeEdit();
            setTimeout(() => location.reload(), 1000);
        } catch (err) { console.error("Error:", err); showToast("Server error: " + err.message, "error"); }
    });
}

// ==================== INIT MODAL RULE ====================
function initModalRule() {
    const editButtons = document.querySelectorAll(".open-edit");
    editModal = document.getElementById("editModal");
    if (!editModal) return;
    editButtons.forEach(btn => {
        btn.addEventListener("click", function(e) {
            e.preventDefault();
            editModal.classList.add("show");
            const rule = window.rule_sensor || window.rule;
            if (document.getElementById("ph_baik_min")) {
                document.getElementById("ph_baik_min").value = rule.ph_min_good || 7.5;
                document.getElementById("ph_baik_max").value = rule.ph_max_good || 8.5;
                document.getElementById("ph_warn_min").value = rule.ph_min_warning || 7.0;
                document.getElementById("ph_warn_max").value = rule.ph_max_warning || 7.4;
                document.getElementById("ph_warn_min_high").value = rule.ph_min_warning_high || 8.6;
                document.getElementById("ph_warn_max_high").value = rule.ph_max_warning_high || 8.9;
                document.getElementById("ph_danger_low").value = rule.ph_danger_low || 6.5;
                document.getElementById("ph_danger_high").value = rule.ph_danger_high || 9.0;
                document.getElementById("tur_baik_min").value = rule.turbidity_min_good || 25;
                document.getElementById("tur_baik_max").value = rule.turbidity_max_good || 50;
                document.getElementById("tur_warn_min").value = rule.turbidity_min_warning || 51;
                document.getElementById("tur_warn_max").value = rule.turbidity_max_warning || 70;
                document.getElementById("tur_danger_low").value = rule.turbidity_danger_low || 10;
                document.getElementById("tur_danger_high").value = rule.turbidity_danger_high || 70;
                updateBahayaDisplay();
            }
        });
    });
}

// ==================== SAVE PENGATURAN ====================
function initSavePengaturan() {
    document.getElementById("btnSimpan")?.addEventListener("click", async () => {
        const penjaga = [...document.querySelectorAll(".penjagaInput")].map(e => e.value).filter(Boolean);
        const wa = [...document.querySelectorAll(".waInput")].map(e => e.value.replace(/\D/g, "")).filter(v => v.startsWith("62"));
        const waktu = [...document.querySelectorAll(".waktuInput")].map(e => e.value).filter(Boolean);
        const tanggal = tanggalInput?.value || null;
        try {
            const res = await fetch("/pengaturan/store", { 
                method: "POST", 
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content }, 
                body: JSON.stringify({ rule_sensor: window.rule_sensor, pengingat: { penjaga, wa: wa, waktu, tanggal, template_pesan: templatePesan?.value || "" } }) 
            });
            const data = await res.json();
            if (!res.ok) { showToast(data.message || "Gagal", "error"); return; }
            showToast("Berhasil disimpan", "success");
        } catch (e) { console.error(e); showToast("Server error", "error"); }
    });
}

// ==================== RESET FORM ====================
function initResetForm() {
    document.getElementById("btnReset")?.addEventListener("click", () => {
        if (penjagaContainer) penjagaContainer.innerHTML = "";
        if (waContainer) waContainer.innerHTML = "";
        if (waktuContainer) waktuContainer.innerHTML = "";
        if (tanggalInput) tanggalInput.value = "";
        updatePreview();
        showToast("Form berhasil direset", "success");
    });
}

// ==================== JADWAL PENGINGAT ====================
async function loadJadwalList() {
    try {
        const response = await fetch('/api/jadwal-list');
        const data = await response.json();
        const container = document.getElementById('jadwalContainer');
        if (!container) return;
        
        if (data.success && data.data && data.data.length > 0) {
            container.innerHTML = data.data.map(jadwal => `
                <div class="jadwal-item" data-id="${jadwal.id}">
                    <div class="jadwal-info">
                        <span class="jadwal-time">⏰ ${jadwal.jam}</span>
                        <span class="jadwal-message">💬 ${escapeHtml(jadwal.pesan)}</span>
                        <span class="jadwal-status ${jadwal.is_sent ? 'sent' : 'pending'}">
                            ${jadwal.is_sent ? '✅ Terkirim' : '⏳ Pending'}
                        </span>
                    </div>
                    <button class="jadwal-delete" onclick="deleteJadwal(${jadwal.id})">🗑 Hapus</button>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div class="jadwal-empty">📭 Belum ada jadwal pengingat. Tambahkan di atas.</div>';
        }
        updatePreview();
    } catch (error) {
        console.error('Error loading jadwal:', error);
    }
}

async function addJadwal() {
    const jam = document.getElementById('newJam')?.value;
    const pesan = document.getElementById('newPesan')?.value;
    const targetNomor = document.getElementById('newTargetNomor')?.value;
    
    if (!jam) { showToast('Pilih jam terlebih dahulu!', 'error'); return; }
    if (!pesan) { showToast('Masukkan pesan pengingat!', 'error'); return; }
    
    let nomorArray = targetNomor ? targetNomor.split(',').map(n => n.trim()).filter(n => n) : [];
    
    try {
        const response = await fetch('/api/jadwal-store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: JSON.stringify({ jam: jam, pesan: pesan, target_nomor: nomorArray })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('✅ Jadwal berhasil ditambahkan', 'success');
            document.getElementById('newJam').value = '';
            document.getElementById('newPesan').value = '';
            document.getElementById('newTargetNomor').value = '';
            loadJadwalList();
        } else {
            showToast(result.message || 'Gagal', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Server error', 'error');
    }
}

async function deleteJadwal(id) {
    if (!confirm('Hapus jadwal ini?')) return;
    try {
        const response = await fetch(`/api/jadwal-delete/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('✅ Jadwal dihapus', 'success');
            loadJadwalList();
        } else {
            showToast(result.message || 'Gagal', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Server error', 'error');
    }
}

// ==================== MONITORING TAMBAK ====================
async function loadRealtimeSensor() {
    try {
        const response = await fetch('/api/pengaturan/realtime-sensor');
        const data = await response.json();
        
        if (data.success) {
            const statusBox = document.getElementById('statusBox');
            if (statusBox) {
                statusBox.innerHTML = `
                    <div class="sensor-item">
                        <span class="sensor-label">📊 Status Air:</span>
                        <span class="sensor-value">${data.ph_status || 'Normal'}</span>
                    </div>
                    <div class="sensor-item">
                        <span class="sensor-label">🧪 pH:</span>
                        <span class="sensor-value">${data.ph || '--'} (${data.ph_status || 'Normal'})</span>
                    </div>
                    <div class="sensor-item">
                        <span class="sensor-label">💧 Kekeruhan:</span>
                        <span class="sensor-value">${data.turbidity || '--'} NTU (${data.turbidity_status || 'Normal'})</span>
                    </div>
                    <div class="sensor-item">
                        <span class="sensor-label">🕐 Last update:</span>
                        <span class="sensor-value">${data.last_update || '-'}</span>
                    </div>
                `;
            }
        }
    } catch (error) {
        console.error('Error loading sensor:', error);
        const statusBox = document.getElementById('statusBox');
        if (statusBox) {
            statusBox.innerHTML = '<div class="sensor-status-loading">❌ Gagal memuat data sensor</div>';
        }
    }
}

// ==================== FEEDING RECOMMENDATION ====================
function fetchFeedingRecommendation() {
    console.log("🔄 Fetching feeding recommendation...");
    
    fetch('/api/feeding/recommendation')
        .then(response => response.json())
        .then(data => {
            console.log("📦 Response feeding:", data);
            
            const beratHighlight = document.getElementById('beratHighlight');
            if (beratHighlight && data.success && data.data) {
                let rekomendasi = data.data.pakan_rekomendasi_kg || 0;
                beratHighlight.innerHTML = `<span class="value-number">${rekomendasi}</span><span class="value-unit">kg</span>`;
                
                const status = data.data.status_keseluruhan;
                const numberSpan = beratHighlight.querySelector('.value-number');
                if (numberSpan) {
                    if (status === 'bahaya') {
                        numberSpan.style.color = '#dc2626';
                    } else if (status === 'peringatan') {
                        numberSpan.style.color = '#f59e0b';
                    } else {
                        numberSpan.style.color = 'white';
                    }
                }
                
                // Update recommendation status text
                const recommendationStatus = document.getElementById('recommendationStatus');
                if (recommendationStatus) {
                    recommendationStatus.innerHTML = data.data.keterangan || '✅ Kualitas air optimal';
                    if (status === 'bahaya') {
                        recommendationStatus.style.color = '#dc2626';
                    } else if (status === 'peringatan') {
                        recommendationStatus.style.color = '#f59e0b';
                    } else {
                        recommendationStatus.style.color = '#10b981';
                    }
                }
            } else {
                console.error("❌ Invalid response structure:", data);
                const beratHighlight = document.getElementById('beratHighlight');
                if (beratHighlight) {
                    beratHighlight.innerHTML = `<span class="value-number">0.5</span><span class="value-unit">kg</span>`;
                }
            }
        })
        .catch(error => {
            console.error('❌ Error fetching feeding recommendation:', error);
            const beratHighlight = document.getElementById('beratHighlight');
            if (beratHighlight) {
                beratHighlight.innerHTML = `<span class="value-number">0.5</span><span class="value-unit">kg</span>`;
            }
        });
}

// ==================== CEK DATA KOSONG ====================
function checkEmptyData() {
    const alertDiv = document.getElementById('dataAlert');
    const alertList = document.getElementById('alertList');
    if (!alertList) return;
    
    let missingData = [];
    const penjagaInputs = document.querySelectorAll(".penjagaInput");
    let penjagaFilled = false;
    penjagaInputs.forEach(input => { if (input.value.trim() !== '') penjagaFilled = true; });
    if (!penjagaFilled && penjagaInputs.length === 0) missingData.push('👨‍🌾 Nama penjaga belum diisi');
    
    const waInputs = document.querySelectorAll(".waInput");
    let waFilled = false;
    waInputs.forEach(input => { if (input.value.trim() !== '') waFilled = true; });
    if (!waFilled && waInputs.length === 0) missingData.push('📱 Nomor WhatsApp belum diisi');
    
    const waktuInputs = document.querySelectorAll(".waktuInput");
    let waktuFilled = false;
    waktuInputs.forEach(input => { if (input.value.trim() !== '') waktuFilled = true; });
    if (!waktuFilled && waktuInputs.length === 0) missingData.push('⏰ Waktu pakan belum diisi');
    
    const tanggal = document.getElementById('tanggalInput')?.value;
    if (!tanggal) missingData.push('📅 Tanggal belum diisi');
    
    const template = document.getElementById('templatePesan')?.value;
    if (!template || template.trim() === '') missingData.push('💬 Template pesan WhatsApp belum diisi');
    
    const jadwalItems = document.querySelectorAll('.jadwal-item');
    if (jadwalItems.length === 0) missingData.push('⏰ Belum ada jadwal pengingat WhatsApp (isi di bawah)');
    
    if (missingData.length > 0) {
        alertList.innerHTML = missingData.map(item => `<li>${item}</li>`).join('');
        alertDiv.style.display = 'block';
    } else { 
        alertDiv.style.display = 'none'; 
    }
}

function closeDataAlert() {
    const alertDiv = document.getElementById('dataAlert');
    if (alertDiv) { 
        alertDiv.style.opacity = '0'; 
        setTimeout(() => { alertDiv.style.display = 'none'; alertDiv.style.opacity = '1'; }, 300); 
    }
}

// ==================== ESCAPE HTML ====================
function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}
// ==================== FEEDING RECOMMENDATION V2 ====================
function fetchFeedingRecommendationV2() {
    console.log("🔄 Fetching feeding recommendation V2...");
    
    fetch('/api/pengaturan/feeding-recommendation-v2')
        .then(response => response.json())
        .then(data => {
            console.log("📦 Response feeding V2:", data);
            
            const beratHighlight = document.getElementById('beratHighlight');
            if (beratHighlight && data.success && data.data) {
                let rekomendasi = data.data.pakan_rekomendasi_kg || 0;
                let status = data.data.status_keseluruhan || 'normal';
                let keterangan = data.data.keterangan || '✅ Kualitas air optimal';
                
                beratHighlight.innerHTML = `<span class="value-number">${rekomendasi}</span><span class="value-unit">kg</span>`;
                
                const numberSpan = beratHighlight.querySelector('.value-number');
                if (numberSpan) {
                    if (status === 'bahaya') {
                        numberSpan.style.color = '#dc2626';
                    } else if (status === 'peringatan') {
                        numberSpan.style.color = '#f59e0b';
                    } else {
                        numberSpan.style.color = 'white';
                    }
                }
                
                // Update recommendation status text
                const recommendationStatus = document.getElementById('recommendationStatus');
                if (recommendationStatus) {
                    recommendationStatus.innerHTML = keterangan;
                    if (status === 'bahaya') {
                        recommendationStatus.style.color = '#dc2626';
                    } else if (status === 'peringatan') {
                        recommendationStatus.style.color = '#f59e0b';
                    } else {
                        recommendationStatus.style.color = '#10b981';
                    }
                }
            } else {
                console.error("❌ Invalid response structure:", data);
                const beratHighlight = document.getElementById('beratHighlight');
                if (beratHighlight) {
                    beratHighlight.innerHTML = `<span class="value-number">0.5</span><span class="value-unit">kg</span>`;
                }
            }
        })
        .catch(error => {
            console.error('❌ Error fetching feeding recommendation V2:', error);
            const beratHighlight = document.getElementById('beratHighlight');
            if (beratHighlight) {
                beratHighlight.innerHTML = `<span class="value-number">0.5</span><span class="value-unit">kg</span>`;
            }
        });
}

// ==================== LOAD REALTIME SENSOR V2 ====================
async function loadRealtimeSensorV2() {
    try {
        const response = await fetch('/api/pengaturan/realtime-sensor-v2');
        const data = await response.json();
        
        if (data.success) {
            const statusBox = document.getElementById('statusBox');
            if (statusBox) {
                // Tampilkan data dengan status
                let phColor = data.ph_status === 'Bahaya' ? '#dc2626' : (data.ph_status === 'Peringatan' ? '#f59e0b' : '#10b981');
                let turColor = data.turbidity_status === 'Bahaya' ? '#dc2626' : (data.turbidity_status === 'Peringatan' ? '#f59e0b' : '#10b981');
                
                statusBox.innerHTML = `
                    <div class="sensor-item">
                        <span class="sensor-label">📊 Status Air:</span>
                        <span class="sensor-value" style="color: ${data.ph_status === 'Bahaya' || data.turbidity_status === 'Bahaya' ? '#dc2626' : '#10b981'}">
                            ${data.ph_status === 'Bahaya' || data.turbidity_status === 'Bahaya' ? '⚠️ Bahaya' : (data.ph_status === 'Peringatan' || data.turbidity_status === 'Peringatan' ? '⚡ Peringatan' : '✅ Normal')}
                        </span>
                    </div>
                    <div class="sensor-item">
                        <span class="sensor-label">🧪 pH:</span>
                        <span class="sensor-value" style="color: ${phColor}">${data.ph || '--'} (${data.ph_status || 'Normal'})</span>
                    </div>
                    <div class="sensor-item">
                        <span class="sensor-label">💧 Kekeruhan:</span>
                        <span class="sensor-value" style="color: ${turColor}">${data.turbidity || '--'} NTU (${data.turbidity_status || 'Normal'})</span>
                    </div>
                    <div class="sensor-item">
                        <span class="sensor-label">🦐 Rekomendasi Pakan:</span>
                        <span class="sensor-value" style="font-weight: bold; color: #667eea;">${data.rekomendasi_kg || 0} kg</span>
                    </div>
                    <div class="sensor-item">
                        <span class="sensor-label">🕐 Last update:</span>
                        <span class="sensor-value">${data.last_update || '-'}</span>
                    </div>
                `;
            }
        }
    } catch (error) {
        console.error('Error loading sensor V2:', error);
        const statusBox = document.getElementById('statusBox');
        if (statusBox) {
            statusBox.innerHTML = '<div class="sensor-status-loading">❌ Gagal memuat data sensor</div>';
        }
    }
}
// ==================== AUTO UPDATE ====================
document.addEventListener("input", function(e) {
    if (e.target.id === "ph_danger_low" || e.target.id === "ph_danger_high" || e.target.id === "tur_danger_low" || e.target.id === "tur_danger_high") {
        updateBahayaDisplay();
    }
});

// ==================== DOM CONTENT LOADED ====================
document.addEventListener('DOMContentLoaded', () => {
    console.log("JS PENGATURAN READY");
    
    // Initialize DOM elements
    editModal = document.getElementById("editModal");
    penjagaContainer = document.getElementById("penjagaContainer");
    waContainer = document.getElementById("waContainer");
    waktuContainer = document.getElementById("waktuContainer");
    tanggalInput = document.getElementById("tanggalInput");
    templatePesan = document.getElementById("templatePesan");
    
    // Initialize all functions
    initAddButtons();
    loadExistingData();
    initModalRule();
    initSaveRule();
    initSavePengaturan();
    initResetForm();
    loadJadwalList();
    
    // 🔥 GANTI DENGAN V2
    loadRealtimeSensorV2(); // <-- GANTI INI
    fetchFeedingRecommendationV2(); // <-- GANTI INI
    
    // Set intervals for auto refresh
    setInterval(loadRealtimeSensorV2, 5000); // <-- GANTI INI
    setInterval(fetchFeedingRecommendationV2, 10000); // <-- GANTI INI
    setInterval(loadJadwalList, 30000);
    
    // Initial check
    setTimeout(checkEmptyData, 500);
    
    // Event listeners for data validation
    document.addEventListener('input', function(e) {
        if (e.target.classList && (e.target.classList.contains('penjagaInput') || 
            e.target.classList.contains('waInput') || 
            e.target.classList.contains('waktuInput') || 
            e.target.id === 'tanggalInput' || 
            e.target.id === 'templatePesan')) {
            checkEmptyData();
        }
    });
    
    // Add button listeners for data validation
    ['addPenjaga', 'addWa', 'addWaktu', 'addJadwal'].forEach(btnId => { 
        const btn = document.getElementById(btnId); 
        if (btn) btn.addEventListener('click', () => setTimeout(checkEmptyData, 500)); 
    });
    
    // Add jadwal button listener
    const addJadwalBtn = document.getElementById('addJadwal');
    if (addJadwalBtn) {
        addJadwalBtn.addEventListener('click', addJadwal);
    }
    
    // Preview tanggal
    if (tanggalInput) {
        tanggalInput.addEventListener('change', updatePreview);
        updatePreview();
    }
});

// ==================== EXPORT FUNCTIONS TO GLOBAL SCOPE ====================
window.addJadwal = addJadwal;
window.deleteJadwal = deleteJadwal;
window.closeDataAlert = closeDataAlert;
window.updatePreview = updatePreview;
window.fetchFeedingRecommendation = fetchFeedingRecommendation;
window.fetchFeedingRecommendationV2 = fetchFeedingRecommendationV2;
window.loadRealtimeSensorV2 = loadRealtimeSensorV2;