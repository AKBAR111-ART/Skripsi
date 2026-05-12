document.addEventListener("DOMContentLoaded", () => {

    console.log("HOME READY");

    initCharts();
    updateDashboard();

    // REALTIME TIAP 1 DETIK
    setInterval(updateDashboard, 1000);
});


// ======================
// GLOBAL CHART STORAGE
// ======================
let charts = {};


// ======================
// MODAL STATE
// ======================
let editModalOpen = false;


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
// FETCH DATA REALTIME
// ======================
async function getSensorData() {

    try {

        // 🔥 FIX REALTIME API
        const res = await fetch("/sensor/latest");

        const data = await res.json();

        console.log("REALTIME:", data);

        return data;

    } catch (err) {

        console.log("Error ambil data:", err);

        return null;
    }
}


// ======================
// UPDATE DASHBOARD
// ======================
async function updateDashboard() {

    const data = await getSensorData();

    if (!data) return;

    let ph = parseFloat(data.ph ?? 7);
    let turb = parseFloat(data.turbidity ?? 20);

    if (isNaN(ph)) ph = 7;
    if (isNaN(turb)) turb = 20;

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

    animateNumber(phText, ph, "", 500);
    animateNumber(turbText, turb, " NTU", 500);

    if (alertBox) {
        alertBox.className = "alert-box";
        alertBox.innerText = "-";
    }

    let pakan = 2.5;
    let kondisi = "Aman";
    let aksi = "Lanjutkan pemberian pakan";
    let penyebab = "Kondisi stabil";

    // ======================
    // TURBIDITY RULE
    // ======================
    if (turb > 50) {

        turbStatus.innerText = "Keruh";
        turbStatus.className = "badge red";

        pakan -= 1;

        kondisi = "Buruk";
        aksi = "Kurangi pakan & cek air";
        penyebab = "Air terlalu keruh";

    } else if (turb > 25) {

        turbStatus.innerText = "Sedang";
        turbStatus.className = "badge yellow";

        pakan -= 0.5;

    } else {

        turbStatus.innerText = "Jernih";
        turbStatus.className = "badge green";
    }

    // ======================
    // PH RULE
    // ======================
    if (ph < 6.5) {

        phStatus.innerText = "Asam";
        phStatus.className = "badge red";

        kondisi = "Bahaya";
        aksi = "Naikkan pH air";
        penyebab = "pH terlalu rendah";

    } else if (ph <= 8) {

        phStatus.innerText = "Normal";
        phStatus.className = "badge green";

    } else {

        phStatus.innerText = "Basa";
        phStatus.className = "badge yellow";

        kondisi = "Perlu perhatian";
        aksi = "Turunkan pH";
        penyebab = "pH terlalu tinggi";
    }

    animateNumber(feedValue, pakan, " Kg", 700);

    if (topWater) topWater.innerText = kondisi;
    if (topStatus) topStatus.innerText = kondisi;

    if (pondCondition) pondCondition.innerText = kondisi;
    if (pondCause) pondCause.innerText = penyebab;
    if (pondAction) pondAction.innerText = aksi;

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


// ======================================================
// MODAL EDIT SYSTEM
// ======================================================

// OPEN MODAL
function openEdit() {

    const modal = document.getElementById("editModal");

    if (modal) modal.style.display = "flex";

    editModalOpen = true;
}


// CLOSE MODAL
function closeEdit() {

    const modal = document.getElementById("editModal");

    if (modal) modal.style.display = "none";

    editModalOpen = false;
}


// ======================
// KIRIM DARI EDIT MODAL
// ======================
function sendEdit() {

    let value = document.getElementById("manualPakan").value;

    if (!value) {

        alert("Input pakan kosong!");

        return;
    }

    sendToDB("manual_real", value);

    closeEdit();
}


// ======================
// KIRIM OTOMATIS
// ======================
function kirimPakan() {

    let jam = new Date().getHours();

    let value = document.getElementById("feedValue")
        .innerText.replace(" Kg", "");

    let type = "";

    if (jam >= 5 && jam <= 10)
        type = "real_pakan_pagi";

    else if (jam >= 11 && jam <= 14)
        type = "real_pakan_siang";

    else if (jam >= 15 && jam <= 18)
        type = "real_pakan_sore";

    else if (jam >= 19 && jam <= 22)
        type = "real_pakan_malam";

    else {

        alert("Diluar jam pakan");

        return;
    }

    sendToDB(type, value);
}


// ======================
// SEND TO DATABASE
// ======================
function sendToDB(type, value) {

    fetch("/feeding/kirim", {

        method: "POST",

        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN":
                document.querySelector('meta[name="csrf-token"]')
                .getAttribute('content')
        },

        body: JSON.stringify({
            type: type,
            value: value
        })

    })
    .then(res => res.json())
    .then(res => {

        console.log(res);

        alert("Data berhasil diupdate ke database!");

    })
    .catch(err => console.log(err));
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
            plugins: {
                legend: {
                    display: false
                }
            }
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
            plugins: {
                legend: {
                    display: false
                }
            }
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

            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

function updateGauge(id, value, max) {

    if (!charts[id]) return;

    const chart = charts[id];

    chart.data.datasets[0].data = [
        value,
        max - value
    ];

    chart.update();
}