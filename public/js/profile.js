document.addEventListener("DOMContentLoaded", function () {

    console.log("PROFILE READY");
    // ======================
    // AUTO CLOSE TOAST
    // ======================
    const toast = document.getElementById("toast");

    if (toast) {

        setTimeout(() => {

            toast.style.opacity = "0";
            toast.style.transform = "translateX(100%)";

            setTimeout(() => {
                toast.remove();
            }, 500);

        }, 3000);
    }
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
    const budidayaModal = document.getElementById('budidayaModal');

    if (event.target === editModal) {
        closeEditModal();
    }

    if (event.target === biomassaModal) {
        closeBiomassa();
    }

    if (event.target === budidayaModal) {
        closeBudidayaModal();
    }

});


// ======================
// MODAL BUDIDAYA
// ======================
function openBudidayaModal() {
    document.getElementById('budidayaModal').style.display = 'block';
    document.body.classList.add('modal-open');
}

function closeBudidayaModal() {
    document.getElementById('budidayaModal').style.display = 'none';
    document.body.classList.remove('modal-open');
}


// ======================
// RESET BUDIDAYA
// ======================
function resetBudidaya() {

    if (!confirm("Yakin ingin reset masa budidaya?")) return;

    fetch('/budidaya/reset', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {

        // hapus toast lama
        const oldToast = document.getElementById("toast");
        if (oldToast) oldToast.remove();

        // buat toast baru
        const toast = document.createElement("div");

        toast.id = "toast";
        toast.className = "toast success";
        toast.innerHTML = "✅ " + data.message;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = "0";
            toast.style.transform = "translateX(100%)";

            setTimeout(() => {
                location.reload();
            }, 500);

        }, 2000);

    })
    .catch(err => {

        const toast = document.createElement("div");

        toast.className = "toast error";
        toast.innerHTML = "❌ Gagal reset budidaya";

        document.body.appendChild(toast);

    });
        const inputFoto = document.querySelector('input[name="foto_tambak"]');

    if (inputFoto) {

        inputFoto.addEventListener('change', function (e) {

            const file = e.target.files[0];

            if (!file) return;

            const reader = new FileReader();

            reader.onload = function(event) {

                const preview = document.getElementById('previewFoto');

                preview.src = event.target.result;
                preview.style.display = 'block';
            };

            reader.readAsDataURL(file);

        });
    }
}