document.addEventListener("DOMContentLoaded", () => {

    // ======================
    // RANDOM DATA
    // ======================
    let ph = parseFloat((Math.random() * (8.5 - 6) + 6).toFixed(1));
    let turb = Math.floor(Math.random() * 80);

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
    animateNumber(phText, 0, ph, 800);
    animateNumber(turbText, 0, turb, 800, " NTU");

    // ======================
    // LOGIC
    // ======================
    let pakan = 2.5;
    let status = "Aman";

    if (turb > 50) {
        turbStatus.innerText = "Keruh";
        turbStatus.className = "badge red";
        pakan -= 1;
        status = "Bahaya";

        alertBox.classList.add("danger");
        alertBox.innerText = "⚠ ALERT: Air Keruh! Kurangi Pakan!";
    } else if (turb > 25) {
        turbStatus.innerText = "Sedang";
        turbStatus.className = "badge yellow";
        pakan -= 0.5;
    } else {
        turbStatus.innerText = "Jernih";
        turbStatus.className = "badge green";
    }

    if (ph < 6.5) {
        phStatus.innerText = "Asam";
        phStatus.className = "badge red";
    } else if (ph <= 8) {
        phStatus.innerText = "Normal";
        phStatus.className = "badge green";
    } else {
        phStatus.innerText = "Basa";
        phStatus.className = "badge yellow";
    }

    animateNumber(feedValue, 0, pakan, 1000, " Kg");

    // ======================
    // GAUGE ANIMASI
    // ======================
    createGauge("phGauge", ph, 10, "#2ecc71");
    createGauge("turbGauge", turb, 100, "#f1c40f");

    // ======================
    // CHART PREMIUM
    // ======================
    createChart("chartPh", "#3498db", [6.5,7,6.6,6.4,6.7,6.2,6.6]);
    createChart("chartTurb", "#f39c12", [20,15,30,28,45,40,60]);
    createBarChart("chartFeed", [2,3,5,3,4,2,3]);

});


// ======================
// ANIMASI ANGKA
// ======================
function animateNumber(el, start, end, duration, suffix = "") {
    let startTime = null;

    function step(timestamp) {
        if (!startTime) startTime = timestamp;
        let progress = timestamp - startTime;
        let value = Math.min(start + (end - start) * (progress / duration), end);

        el.innerText = value.toFixed(1) + suffix;

        if (progress < duration) requestAnimationFrame(step);
    }

    requestAnimationFrame(step);
}


// ======================
// GAUGE
// ======================
function createGauge(id, value, max, color) {
    new Chart(document.getElementById(id), {
        type: "doughnut",
        data: {
            datasets: [{
                data: [value, max - value],
                backgroundColor: [color, "#ecf0f1"],
                borderWidth: 0
            }]
        },
        options: {
            rotation: -90,
            circumference: 180,
            cutout: "75%",
            animation: {
                animateRotate: true,
                duration: 1200
            },
            plugins: { legend: { display: false } }
        }
    });
}


// ======================
// LINE CHART PREMIUM
// ======================
function createChart(id, color, data) {
    const ctx = document.getElementById(id).getContext("2d");

    const gradient = ctx.createLinearGradient(0, 0, 0, 200);
    gradient.addColorStop(0, color);
    gradient.addColorStop(1, "transparent");

    new Chart(ctx, {
        type: "line",
        data: {
            labels: ["1.3","1.4","1.5","1.6","1.7","1.8","1.9"],
            datasets: [{
                data: data,
                borderColor: color,
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            animation: {
                duration: 1500
            }
        }
    });
}


// ======================
// BAR CHART
// ======================
function createBarChart(id, data) {
    new Chart(document.getElementById(id), {
        type: "bar",
        data: {
            labels: ["1.3","1.4","1.5","1.6","1.7","1.8","1.9"],
            datasets: [{
                data: data,
                backgroundColor: "#3498db",
                borderRadius: 10
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            animation: {
                duration: 1200
            }
        }
    });
}