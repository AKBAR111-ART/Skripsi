document.addEventListener("DOMContentLoaded", () => {

    console.log("HOME READY");

    initCharts();        // buat chart sekali
    updateDashboard();   // load pertama
    setInterval(updateDashboard, 5000); // update data

});


// ======================
// GLOBAL CHART STORAGE
// ======================
let charts = {};


// ======================
// INIT CHART (HANYA SEKALI)
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
        const res = await fetch("/api/sensor/latest");
        return await res.json();
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

    let ph = parseFloat(data.ph);
    let turb = parseFloat(data.turbidity);

    // ======================
    // ELEMENT
    // ======================
    const phText = document.getElementById("phText");
    const turbText = document.getElementById("turbText");
    const feedValue = document.getElementById("feedValue");

    const phStatus = document.getElementById("phStatus");
    const turbStatus = document.getElementById("turbStatus");

    const alertBox = document.getElementById("alertBox");

    // ======================
    // ANIMASI ANGKA
    // ======================
    animateNumber(phText, ph, "", 500);
    animateNumber(turbText, turb, " NTU", 500);

    // ======================
    // LOGIC
    // ======================
    let pakan = 2.5;

    if (turb > 50) {
        turbStatus.innerText = "Keruh";
        turbStatus.className = "badge red";
        pakan -= 1;

        alertBox.classList.add("danger");
        alertBox.innerText = "⚠ ALERT: Air Keruh! Kurangi Pakan!";
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

    if (ph < 6.5) {
        phStatus.innerText = "Asam";
        phStatus.className = "badge red";
    } 
    else if (ph <= 8) {
        phStatus.innerText = "Normal";
        phStatus.className = "badge green";
    } 
    else {
        phStatus.innerText = "Basa";
        phStatus.className = "badge yellow";
    }

    animateNumber(feedValue, pakan, " Kg", 700);

    // ======================
    // UPDATE CHART DATA
    // ======================
    updateLineChart("chartPh", ph);
    updateLineChart("chartTurb", turb);
    updateBarChart("chartFeed", pakan);

    updateGauge("phGauge", ph, 10);
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
// CREATE LINE CHART
// ======================
function createLineChart(id, color) {

    const canvas = document.getElementById(id);
    if (!canvas) return;

    const ctx = canvas.getContext("2d");

    const gradient = ctx.createLinearGradient(0, 0, 0, 200);
    gradient.addColorStop(0, color);
    gradient.addColorStop(1, "transparent");

    charts[id] = new Chart(ctx, {
        type: "line",
        data: {
            labels: [],
            datasets: [{
                data: [],
                borderColor: color,
                backgroundColor: gradient,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                x: { display: false },
                y: { display: true }
            }
        }
    });
}


// ======================
// UPDATE LINE CHART
// ======================
function updateLineChart(id, value) {

    if (!charts[id]) return;

    const chart = charts[id];

    chart.data.labels.push("");
    chart.data.datasets[0].data.push(value);

    if (chart.data.labels.length > 7) {
        chart.data.labels.shift();
        chart.data.datasets[0].data.shift();
    }

    chart.update();
}


// ======================
// CREATE BAR CHART
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
                backgroundColor: "#3498db",
                borderRadius: 10
            }]
        },
        options: {
            plugins: { legend: { display: false } }
        }
    });
}


// ======================
// UPDATE BAR CHART
// ======================
function updateBarChart(id, value) {

    if (!charts[id]) return;

    const chart = charts[id];

    chart.data.labels.push("");
    chart.data.datasets[0].data.push(value);

    if (chart.data.labels.length > 7) {
        chart.data.labels.shift();
        chart.data.datasets[0].data.shift();
    }

    chart.update();
}


// ======================
// CREATE GAUGE
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


// ======================
// UPDATE GAUGE
// ======================
function updateGauge(id, value, max) {

    if (!charts[id]) return;

    const chart = charts[id];

    chart.data.datasets[0].data = [value, max - value];
    chart.update();
}