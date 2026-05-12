document.addEventListener("DOMContentLoaded", () => {

    console.log("JS PENGATURAN READY");

    document.addEventListener("click", function (e) {
        if (e.target.closest(".open-edit")) {
            openEdit();
        }
    });

    // ======================
    // TOAST SYSTEM
    // ======================
    function showToast(message, type = "success") {
        let toast = document.createElement("div");
        toast.className = `toast show ${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <span>${type === "success" ? "✔️" : "❌"}</span>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    window.showToast = showToast;

    // ======================
    // RULE DEFAULT (WAJIB BIAR TIDAK NULL)
    // ======================
    window.rule_sensor = {
        ph: { low: [6.5, 7.5], normal: [7.5, 8.5], high: [8.5, 14] },
        turbidity: { low: [0, 10], normal: [10, 50], high: [50, 100] }
    };

    // ======================
    // SAVE BUTTON (FIX 422 ERROR)
    // ======================
    document.getElementById("btnSimpan")?.addEventListener("click", async () => {

        // ======================
        // RULE SENSOR FINAL (PASTI ADA ISI)
        // ======================
        let rule_sensor = window.rule_sensor;

        // ======================
        // PENGINGAT
        // ======================
        let penjaga = [];
        document.querySelectorAll(".penjagaInput").forEach(i => {
            if (i.value.trim()) penjaga.push(i.value);
        });

        let waktu = [];
        document.querySelectorAll(".waktuInput").forEach(i => {
            if (i.value.trim()) waktu.push(i.value);
        });

        let pengingat = {
            penjaga,
            waktu,
            tanggal: document.getElementById("tanggalInput")?.value || null
        };

        // ======================
        // DEBUG WAJIB
        // ======================
        console.log("KIRIM DATA:", { rule_sensor, pengingat });

        try {
            let res = await fetch("/pengaturan/store", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    rule_sensor,
                    pengingat
                })
            });

            let data = await res.json();

            if (!res.ok) {
                console.error("ERROR:", data);
                showToast(data.message || "Gagal menyimpan", "error");
                return;
            }

            showToast("Berhasil disimpan", "success");

        } catch (err) {
            console.error(err);
            showToast("Gagal request ke server", "error");
        }
    });

});
