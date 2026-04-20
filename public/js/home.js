document.addEventListener("DOMContentLoaded", () => {

    // ===== DATA =====
    const labels = ["1.3","1.4","1.5","1.6","1.7","1.8","1.9"];

    const phData = [6.5,7,6.6,6.4,6.7,6.2,6.6];
    const turbData = [20,15,30,28,45,40,60];
    const feedData = [2,3,5,3,4,2,3];

    // ===== CHART PH =====
    new Chart(document.getElementById("chartPh"), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                data: phData,
                borderColor: "#3498db",
                backgroundColor: "rgba(52,152,219,0.2)",
                fill: true,
                tension: 0.4
            }]
        },
        options: { plugins:{legend:{display:false}} }
    });

    // ===== TURB =====
    new Chart(document.getElementById("chartTurb"), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                data: turbData,
                borderColor: "#f39c12",
                backgroundColor: "rgba(243,156,18,0.2)",
                fill: true,
                tension: 0.4
            }]
        },
        options: { plugins:{legend:{display:false}} }
    });

    // ===== FEED =====
    new Chart(document.getElementById("chartFeed"), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: feedData,
                backgroundColor: "#3498db"
            }]
        },
        options: { plugins:{legend:{display:false}} }
    });

    // ===== GAUGE PH =====
    new Chart(document.getElementById("phGauge"), {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [70,30],
                backgroundColor: ["#2ecc71","#ecf0f1"],
                borderWidth:0
            }]
        },
        options: {
            rotation: -90,
            circumference: 180,
            cutout: "70%",
            plugins:{legend:{display:false}}
        }
    });

    // ===== GAUGE TURB =====
    new Chart(document.getElementById("turbGauge"), {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [50,50],
                backgroundColor: ["#f1c40f","#ecf0f1"],
                borderWidth:0
            }]
        },
        options: {
            rotation: -90,
            circumference: 180,
            cutout: "70%",
            plugins:{legend:{display:false}}
        }
    });

});