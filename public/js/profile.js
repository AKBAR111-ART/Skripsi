document.addEventListener("DOMContentLoaded", function () {

    console.log("PROFILE READY");

    // ======================
    // DASHBOARD INIT
    // ======================
    if (typeof initCharts === "function") {
        initCharts();
    }

    if (typeof updateDashboard === "function") {
        updateDashboard();
        setInterval(updateDashboard, 3000);
    }

    // ======================
    // PROGRESS BAR
    // ======================
    const bar = document.querySelector(".bar");
    if (bar) {
        setTimeout(() => {
            bar.style.width = "56%";
        }, 300);
    }

});


// ======================
// MODAL EDIT PROFILE
// ======================
function openEditModal() {
    const modal = document.getElementById('editModal');
    if (modal) {
        modal.style.display = 'block';
        document.body.classList.add('modal-open');
    }
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
    }
}


// ======================
// MODAL BIOMASSA
// ======================
function openBiomassa() {
    const modal = document.getElementById('biomassaModal');
    if (modal) {
        modal.style.display = 'block';
        document.body.classList.add('modal-open');
    }
}

function closeBiomassa() {
    const modal = document.getElementById('biomassaModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
    }
}



// ======================
// SENSOR CALIBRATION
// ======================
function kalibrasi(type) {

    let noise = Math.floor(Math.random() * 100);

    if (noise < 40) {
        alert("Kalibrasi " + type + " berhasil (" + noise + ")");
    } else {
        alert("Noise tinggi " + noise + " - perlu kalibrasi ulang");
    }
}


// ======================
// CLOSE MODAL OUTSIDE CLICK
// ======================
window.addEventListener('click', function (event) {

    const editModal = document.getElementById('editModal');
    const biomassaModal = document.getElementById('biomassaModal');

    if (event.target === editModal) {
        closeEditModal();
    }

    if (event.target === biomassaModal) {
        closeBiomassa();
    }

});
function openBudidayaModal() {
    document.getElementById('budidayaModal').style.display = 'block';
    document.body.classList.add('modal-open');
}

function closeBudidayaModal() {
    document.getElementById('budidayaModal').style.display = 'none';
    document.body.classList.remove('modal-open');
}
function resetBudidaya() {
    if (!confirm("Yakin ingin reset masa budidaya?")) return;

    fetch('/budidaya/reset', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(res => res.json())
    .then(data => {
        alert("Budidaya berhasil direset");
        location.reload();
    })
    .catch(err => {
        alert("Gagal reset budidaya");
    });
}