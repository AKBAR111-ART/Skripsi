document.addEventListener("DOMContentLoaded", function () {

    // ======================
    // INIT CHART
    // ======================
    let chart;
    const canvas = document.getElementById("weeklyChart");

    if (canvas) {
        const ctx = canvas.getContext("2d");

        chart = new Chart(ctx, {
            type: "line",
            data: {
                labels: [
                    "M1","M2","M3","M4","M5","M6",
                    "M7","M8","M9","M10","M11","M12"
                ],
                datasets: [{
                    label: "pH Air",
                    data: [7.2,7.4,7.1,7.6,7.3,7.5,7.7,7.2,7.4,7.6,7.8,7.5],
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: false
            }
        });
    }

    // ======================
    // UPDATE SENSOR
    // ======================
    function updateSensor() {

        const phEl = document.getElementById("phValue");
        const turbEl = document.getElementById("turbidityValue");

        if (!phEl || !turbEl) return;

        const phStatus = document.getElementById("phStatus");
        const turbStatus = document.getElementById("turbidityStatus");

        const phIcon = document.getElementById("phIcon");
        const turbIcon = document.getElementById("turbidityIcon");

        const phCard = document.querySelector(".card-monitor.ph");
        const turbCard = document.querySelector(".card-monitor.turbidity");

        const rekom = document.getElementById("rekomendasiText");
        const rekomCard = document.querySelector(".rekomendasi-card");
        const feedValue = document.querySelector(".feed-value");

        // ======================
        // DATA SIMULASI
        // ======================
        let ph = parseFloat((Math.random() * (9 - 5) + 5).toFixed(1));
        let turbidity = Math.floor(Math.random() * 100);

        animateValue(phEl, 5, ph, 500);
        turbEl.innerText = turbidity + " NTU";

        // RESET CLASS
        phCard.className = "card-monitor ph";
        turbCard.className = "card-monitor turbidity";
        rekomCard.className = "rekomendasi-card";

        let phState = "normal";
        let turbState = "normal";

        // ======================
        // LOGIC PH
        // ======================
        if (ph >= 6.5 && ph <= 8) {
            phStatus.innerText = "Normal";
            phStatus.className = "status normal";

            phIcon.innerText = "✔";
            phIcon.className = "status-icon normal";

            phCard.classList.add("normal");
            phState = "normal";

        } else if (ph >= 6 && ph < 6.5) {
            phStatus.innerText = "Sedikit Asam";
            phStatus.className = "status warning";

            phIcon.innerText = "⚠";
            phIcon.className = "status-icon warning";

            phCard.classList.add("warning");
            phState = "warning";

        } else {
            phStatus.innerText = "Bahaya";
            phStatus.className = "status danger";

            phIcon.innerText = "✖";
            phIcon.className = "status-icon danger";

            phCard.classList.add("danger");
            phState = "danger";
        }

        // ======================
        // LOGIC TURBIDITY
        // ======================
        if (turbidity < 25) {
            turbStatus.innerText = "Jernih";
            turbStatus.className = "status normal";

            turbIcon.innerText = "✔";
            turbIcon.className = "status-icon normal";

            turbCard.classList.add("normal");
            turbState = "normal";

        } else if (turbidity < 60) {
            turbStatus.innerText = "Keruh";
            turbStatus.className = "status warning";

            turbIcon.innerText = "⚠";
            turbIcon.className = "status-icon warning";

            turbCard.classList.add("warning");
            turbState = "warning";

        } else {
            turbStatus.innerText = "Sangat Keruh";
            turbStatus.className = "status danger";

            turbIcon.innerText = "✖";
            turbIcon.className = "status-icon danger";

            turbCard.classList.add("danger");
            turbState = "danger";
        }

        // ======================
        // RULE BASED (KG)
        // ======================
        let finalState = "normal";
        let baseFeed = 2.5; // kg (default)
        let feed = 0;

        if (phState === "danger" || turbState === "danger") {
            finalState = "danger";
            feed = baseFeed * 0.5;
            rekom.innerText = "🚨 Hentikan pakan! Air dalam kondisi buruk.";

        } else if (phState === "warning" || turbState === "warning") {
            finalState = "warning";
            feed = baseFeed * 0.75;
            rekom.innerText = "⚠ Kurangi pakan, kondisi air kurang stabil.";

        } else {
            finalState = "normal";
            feed = baseFeed;
            rekom.innerText = "✅ Pakan optimal, kondisi air sangat baik.";
        }

        rekomCard.classList.add(finalState);

        // 🔥 OUTPUT KG
        feedValue.innerHTML = feed.toFixed(2) + " <span>Kg</span>";

        // ======================
        // UPDATE CHART
        // ======================
        if (chart) {
            chart.data.datasets[0].data.shift();
            chart.data.datasets[0].data.push(ph);
            chart.update();
        }
    }

    // LOOP
    updateSensor();
    setInterval(updateSensor, 3000);

    // ======================
    // AVATAR CLICK
    // ======================
    const avatar = document.querySelector(".profile-trigger");
    if (avatar) {
        avatar.addEventListener("click", function () {
            if (typeof openProfile === "function") {
                openProfile();
            }
        });
    }

});


// ======================
// ANIMASI ANGKA
// ======================
function animateValue(el, start, end, duration) {
    let startTime = null;

    function step(timestamp) {
        if (!startTime) startTime = timestamp;
        let progress = timestamp - startTime;
        let value = Math.min(start + (end - start) * (progress / duration), end);
        el.innerText = value.toFixed(1);

        if (progress < duration) {
            requestAnimationFrame(step);
        }
    }

    requestAnimationFrame(step);
}