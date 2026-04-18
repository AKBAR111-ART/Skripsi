document.addEventListener("DOMContentLoaded", function () {

    function updateSensor() {

        // ELEMENT
        let phEl = document.getElementById("phValue");
        let turbEl = document.getElementById("turbidityValue");

        let phStatus = document.getElementById("phStatus");
        let turbStatus = document.getElementById("turbidityStatus");

        let phIcon = document.getElementById("phIcon");
        let turbIcon = document.getElementById("turbidityIcon");

        let phCard = document.querySelector(".card-monitor.ph");
        let turbCard = document.querySelector(".card-monitor.turbidity");

        let rekom = document.getElementById("rekomendasiText");
        let rekomCard = document.querySelector(".rekomendasi-card");

        // STOP kalau bukan halaman home
        if (!phEl || !turbEl) return;

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
        // REKOMENDASI CERDAS
        // ======================
        let finalState = "normal";

        if (phState === "danger" || turbState === "danger") {
            finalState = "danger";
            rekom.innerText = "🚨 Hentikan pakan! Air dalam kondisi buruk.";

        } else if (phState === "warning" || turbState === "warning") {
            finalState = "warning";
            rekom.innerText = "⚠ Kurangi pakan, kondisi air kurang stabil.";

        } else {
            finalState = "normal";
            rekom.innerText = "✅ Pakan optimal, kondisi air sangat baik.";
        }

        rekomCard.classList.add(finalState);
    }

    // LOOP SENSOR
    setInterval(updateSensor, 3000);
    updateSensor();

    // ======================
    // FIX AVATAR CLICK
    // ======================
    let avatar = document.querySelector(".profile-trigger");

    if (avatar) {
        avatar.addEventListener("click", function () {
            openProfile();
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
phCard.classList.add("normal"); // atau warning / danger