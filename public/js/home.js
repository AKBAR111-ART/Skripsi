document.addEventListener("DOMContentLoaded", () => {

    console.log("HOME READY");

    initCharts();        
    updateDashboard();   

    setInterval(updateDashboard, 3000); // realtime tiap 3 detik
});


// ======================
// GLOBAL CHART STORAGE
// ======================
let charts = {};


// ======================
// INIT CHART
// ======================
function initCharts() {

    createLineChart("chartPh", "#3498db");
    createLineChart("chartTurb", "#f39c12");
    createBarChart("chartFeed");

    createGauge("phGauge", "#2ecc71");
    createGauge("turbGauge", "#f1c40f");
}


// ======================
// FETCH DATA
// ======================
async function getSensorData() {
    try {
        const res = await fetch("/api/sensor");
        return await res.json();
    } catch (err) {
        console.log("Error ambil data:", err);
        return null;
    }
}


// ======================
// UPDATE DASHBOARD (FIX TOTAL)
// ======================
async function updateDashboard() {

    console.log("UPDATE JALAN");

    const data = await getSensorData();
    console.log("DATA:", data);

    if (!data) return;

    // 🔥 fallback biar gak nol / NaN
    let ph = parseFloat(data.ph ?? 7);
    let turb = parseFloat(data.turbidity ?? 20);

    if (isNaN(ph)) ph = 7;
    if (isNaN(turb)) turb = 20;

    // ======================
    // ELEMENT
    // ======================
    const phText = document.getElementById("phText");
    const turbText = document.getElementById("turbText");
    const feedValue = document.getElementById("feedValue");

    const phStatus = document.getElementById("phStatus");
    const turbStatus = document.getElementById("turbStatus");

    const alertBox = document.getElementById("alertBox");

    const topWater = document.getElementById("topWater");
    const topStatus = document.getElementById("topStatus");

    const pondCondition = document.getElementById("pondCondition");
    const pondCause = document.getElementById("pondCause");
    const pondAction = document.getElementById("pondAction");

    // ======================
    // ANIMASI ANGKA
    // ======================
    animateNumber(phText, ph, "", 500);
    animateNumber(turbText, turb, " NTU", 500);

    // ======================
    // RESET ALERT
    // ======================
    if (alertBox) {
        alertBox.className = "alert-box";
        alertBox.innerText = "-";
    }

    // ======================
    // LOGIC RULE
    // ======================
    let pakan = 2.5;
    let kondisi = "Aman";
    let aksi = "Lanjutkan pemberian pakan";
    let penyebab = "Kondisi stabil";

    // ===== TURBIDITY =====
    if (turb > 50) {
        turbStatus.innerText = "Keruh";
        turbStatus.className = "badge red";
        pakan -= 1;

        kondisi = "Buruk";
        aksi = "Kurangi pakan & cek air";
        penyebab = "Air terlalu keruh";

        if (alertBox) {
            alertBox.classList.add("danger");
            alertBox.innerText = "⚠ Air keruh! Kurangi pakan!";
        }
    } 
    else if (turb > 25) {
        turbStatus.innerText = "Sedang";
        turbStatus.className = "badge yellow";
        pakan -= 0.5;
    } 
    else {
        turbStatus.innerText = "Jernih";
        turbStatus.className = "badge green";
    }

    // ===== PH =====
    if (ph < 6.5) {
        phStatus.innerText = "Asam";
        phStatus.className = "badge red";

        kondisi = "Bahaya";
        aksi = "Naikkan pH air";
        penyebab = "pH terlalu rendah";

        if (alertBox) {
            alertBox.classList.add("danger");
            alertBox.innerText = "⚠ pH terlalu rendah!";
        }
    } 
    else if (ph <= 8) {
        phStatus.innerText = "Normal";
        phStatus.className = "badge green";
    } 
    else {
        phStatus.innerText = "Basa";
        phStatus.className = "badge yellow";

        kondisi = "Perlu perhatian";
        aksi = "Turunkan pH";
        penyebab = "pH terlalu tinggi";
    }

    // ======================
    // UPDATE UI
    // ======================
    animateNumber(feedValue, pakan, " Kg", 700);

    if (topWater) topWater.innerText = kondisi;
    if (topStatus) topStatus.innerText = kondisi;

    if (pondCondition) pondCondition.innerText = kondisi;
    if (pondCause) pondCause.innerText = penyebab;
    if (pondAction) pondAction.innerText = aksi;

    // ======================
    // UPDATE CHART
    // ======================
    updateLineChart("chartPh", ph);
    updateLineChart("chartTurb", turb);
    updateBarChart("chartFeed", pakan);

    updateGauge("phGauge", ph, 14);
    updateGauge("turbGauge", turb, 100);
}


// ======================
// ANIMASI ANGKA
// ======================
function animateNumber(el, value, suffix = "", duration = 500) {
    if (!el) return;

    let start = 0;
    let startTime = null;

    function step(timestamp) {
        if (!startTime) startTime = timestamp;

        let progress = timestamp - startTime;
        let percent = Math.min(progress / duration, 1);
        let current = start + (value - start) * percent;

        el.innerText = current.toFixed(1) + suffix;

        if (percent < 1) requestAnimationFrame(step);
    }

    requestAnimationFrame(step);
}


// ======================
// LINE CHART
// ======================
function createLineChart(id, color) {

    const canvas = document.getElementById(id);
    if (!canvas) return;

    const ctx = canvas.getContext("2d");

    charts[id] = new Chart(ctx, {
        type: "line",
        data: {
            labels: [],
            datasets: [{
                data: [],
                borderColor: color,
                backgroundColor: "transparent",
                tension: 0.4
            }]
        },
        options: {
            plugins: { legend: { display: false } }
        }
    });
}

function updateLineChart(id, value) {

    if (!charts[id]) return;

    const chart = charts[id];

    chart.data.labels.push("");
    chart.data.datasets[0].data.push(value);

    if (chart.data.labels.length > 10) {
        chart.data.labels.shift();
        chart.data.datasets[0].data.shift();
    }

    chart.update();
}


// ======================
// BAR CHART
// ======================
function createBarChart(id) {

    const canvas = document.getElementById(id);
    if (!canvas) return;

    charts[id] = new Chart(canvas, {
        type: "bar",
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: "#3498db"
            }]
        },
        options: {
            plugins: { legend: { display: false } }
        }
    });
}

function updateBarChart(id, value) {

    if (!charts[id]) return;

    const chart = charts[id];

    chart.data.labels.push("");
    chart.data.datasets[0].data.push(value);

    if (chart.data.labels.length > 10) {
        chart.data.labels.shift();
        chart.data.datasets[0].data.shift();
    }

    chart.update();
}


// ======================
// GAUGE
// ======================
function createGauge(id, color) {

    const canvas = document.getElementById(id);
    if (!canvas) return;

    charts[id] = new Chart(canvas, {
        type: "doughnut",
        data: {
            datasets: [{
                data: [0, 100],
                backgroundColor: [color, "#ecf0f1"],
                borderWidth: 0
            }]
        },
        options: {
            rotation: -90,
            circumference: 180,
            cutout: "75%",
            plugins: { legend: { display: false } }
        }
    });
}

function updateGauge(id, value, max) {

    if (!charts[id]) return;

    const chart = charts[id];

    chart.data.datasets[0].data = [value, max - value];
    chart.update();
}