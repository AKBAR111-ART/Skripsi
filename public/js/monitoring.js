document.addEventListener("DOMContentLoaded", () => {

    // =========================
    // GAUGE CHART
    // =========================
    function createGauge(id, value, color) {
        const el = document.getElementById(id);
        if (!el) return;

        return new Chart(el, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [value, 100 - value],
                    backgroundColor: [color, "#ecf0f1"],
                    borderWidth: 0
                }]
            },
            options: {
                rotation: -90,
                circumference: 180,
                cutout: "75%",
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    createGauge("phGauge", 78, "#2ecc71");
    createGauge("turbGauge", 60, "#f1c40f");

    // =========================
    // TABLE MONITORING
    // =========================
    const table = document.getElementById("tableMonitoring");

    if (table) {
        for (let i = 0; i < 6; i++) {
            table.innerHTML += `
                <tr>
                    <td>07:00:${i}0</td>
                    <td>${(6.8 + Math.random()).toFixed(2)}</td>
                    <td>${(30 + Math.random()*5).toFixed(1)} NTU</td>
                    <td style="color:#27ae60">Sesuai</td>
                </tr>
            `;
        }
    }

    // =========================
    // TABLE PAKAN
    // =========================
    const feedTable = document.getElementById("tableFeed");

    if (feedTable) {
        ["07:00","11:00","15:00","19:00"].forEach(jam => {
            feedTable.innerHTML += `
                <tr>
                    <td>${jam}</td>
                    <td>87.5 gram</td>
                    <td style="color:#27ae60">Sesuai</td>
                </tr>
            `;
        });
    }

    // =========================
    // 🔥 ANIMASI PAKAN JATUH (KE AREA UDANG)
    // =========================
    const shrimpArea = document.getElementById("shrimpArea");

    function createFeed() {
        if (!shrimpArea) return;

        const feed = document.createElement("div");
        feed.classList.add("feed");

        // posisi random horizontal
        feed.style.left = Math.random() * shrimpArea.offsetWidth + "px";

        shrimpArea.appendChild(feed);

        // hapus setelah jatuh
        setTimeout(() => {
            feed.remove();
        }, 4000);
    }

    // spawn terus
    setInterval(createFeed, 400);

});