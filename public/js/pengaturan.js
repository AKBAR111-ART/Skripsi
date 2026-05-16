document.addEventListener("DOMContentLoaded", () => {

    console.log("JS PENGATURAN READY");

    // =========================
    // ELEMENT
    // =========================
    const modal = document.getElementById("editModal");

    const penjagaContainer = document.getElementById("penjagaContainer");
    const waContainer = document.getElementById("waContainer");
    const waktuContainer = document.getElementById("waktuContainer");

    const tanggalInput = document.getElementById("tanggalInput");
    const templatePesan = document.getElementById("templatePesan");

    // =========================
    // TOAST
    // =========================
    function showToast(message, type = "success") {
        const toast = document.getElementById("toast");
        if (!toast) return;

        toast.className = `toast show ${type}`;
        document.getElementById("toastMessage").innerText = message;
        document.getElementById("toastIcon").innerText =
            type === "success" ? "✔️" : "❌";

        setTimeout(() => toast.classList.remove("show"), 3000);
    }
window.showToast = showToast;

    // =========================
    // RULE DEFAULT
    // =========================
    window.rule_sensor = window.rule || {
        ph: { baik: [7.5, 8.5], warning: [7.0, 7.4], bahaya: "< 7 atau > 8.5" },
        turbidity: { baik: [10, 50], warning: [51, 70], bahaya: "> 70" }
    };

    // =========================
    // CREATE INPUT
    // =========================
    function createInput(type) {

        const wrapper = document.createElement("div");
        wrapper.style.display = "flex";
        wrapper.style.gap = "10px";
        wrapper.style.marginBottom = "10px";

        const input = document.createElement("input");

        input.className = `${type}Input form-control`;
        input.name = `pengingat[${type}][]`;

        if (type === "waktu") {
            input.type = "time";
        } else if (type === "wa") {
            input.type = "text";
            input.placeholder = "628xxxxxxxxxx";
        } else {
            input.type = "text";
            input.placeholder = "Nama penjaga";
        }

        input.addEventListener("input", updatePreview);

        const btn = document.createElement("button");
        btn.type = "button";
        btn.innerHTML = "✖";
        btn.style.background = "#ffebeb";
        btn.style.border = "none";
        btn.style.padding = "0 15px";
        btn.style.borderRadius = "10px";
        btn.style.cursor = "pointer";

        btn.onclick = () => {
            wrapper.remove();
            updatePreview();
        };

        wrapper.appendChild(input);
        wrapper.appendChild(btn);

        return wrapper;
    }

    // =========================
    // ADD INPUT
    // =========================
    document.getElementById("addPenjaga")?.addEventListener("click", () => {
        penjagaContainer.appendChild(createInput("penjaga"));
    });

    document.getElementById("addWa")?.addEventListener("click", () => {
        waContainer.appendChild(createInput("wa"));
    });

    document.getElementById("addWaktu")?.addEventListener("click", () => {
        waktuContainer.appendChild(createInput("waktu"));
    });

    // =========================
    // PREVIEW
    // =========================
    function updatePreview() {

        const penjaga = [...document.querySelectorAll(".penjagaInput")]
            .map(e => e.value).filter(Boolean);

        const wa = [...document.querySelectorAll(".waInput")]
            .map(e => e.value).filter(Boolean);

        const waktu = [...document.querySelectorAll(".waktuInput")]
            .map(e => e.value).filter(Boolean);

        const elPenjaga = document.getElementById("penjagaNow");
        const elWa = document.getElementById("waNow");
        const elWaktu = document.getElementById("waktuNow");

        if (elPenjaga) elPenjaga.innerText = penjaga.join(", ") || "-";
        if (elWa) elWa.innerText = wa.join(", ") || "-";
        if (elWaktu) elWaktu.innerText = waktu.join(", ") || "-";

        if (tanggalInput) {
            document.getElementById("tanggalNow").innerText =
                tanggalInput.value || "-";
        }
    }

    tanggalInput?.addEventListener("change", updatePreview);

    // =========================
    // RESET
    // =========================
    document.querySelector(".btn-reset")?.addEventListener("click", () => {
        penjagaContainer.innerHTML = "";
        waContainer.innerHTML = "";
        waktuContainer.innerHTML = "";
        if (tanggalInput) tanggalInput.value = "";

        updatePreview();
        showToast("Form berhasil direset", "success");
    });

    // =========================
    // SAVE DATA
    // =========================
    document.getElementById("btnSimpan")?.addEventListener("click", async () => {

        const penjaga = [...document.querySelectorAll(".penjagaInput")]
            .map(e => e.value).filter(Boolean);

        const wa = [...document.querySelectorAll(".waInput")]
            .map(e => e.value.replace(/\D/g, ""))
            .filter(v => v.startsWith("62"));

        const waktu = [...document.querySelectorAll(".waktuInput")]
            .map(e => e.value).filter(Boolean);

        const tanggal = tanggalInput?.value || null;

        const pengingat = {
            penjaga,
            wa,
            waktu,
            tanggal,
            template_pesan: templatePesan?.value || ""
        };

        try {
            const res = await fetch("/pengaturan/store", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    rule_sensor: window.rule_sensor,
                    pengingat
                })
            });

            const data = await res.json();

            if (!res.ok) {
                showToast(data.message || "Gagal", "error");
                return;
            }

            showToast("Berhasil disimpan", "success");

        } catch (e) {
            console.error(e);
            showToast("Server error", "error");
        }
    });

    // =========================
    // LOAD DATA DB
    // =========================
    if (window.pengaturanData) {

        const penjaga = JSON.parse(window.pengaturanData.penjaga || "[]");
        const wa = JSON.parse(window.pengaturanData.nomor_wa || "[]");
        const waktu = JSON.parse(window.pengaturanData.waktu || "[]");

        penjaga.forEach(v => {
            const el = createInput("penjaga");
            el.querySelector("input").value = v;
            penjagaContainer.appendChild(el);
        });

        wa.forEach(v => {
            const el = createInput("wa");
            el.querySelector("input").value = v;
            waContainer.appendChild(el);
        });

        waktu.forEach(v => {
            const el = createInput("waktu");
            el.querySelector("input").value = v;
            waktuContainer.appendChild(el);
        });

        if (window.pengaturanData.tanggal) {
            tanggalInput.value = window.pengaturanData.tanggal;
        }

        if (templatePesan) {
            templatePesan.value = window.pengaturanData.template_pesan || "";
        }

        updatePreview();

    } else {
        document.getElementById("addPenjaga")?.click();
        document.getElementById("addWa")?.click();
        document.getElementById("addWaktu")?.click();
    }

});


// =========================
// MODAL RULE
// =========================
const editButtons = document.querySelectorAll(".open-edit");
const editModal = document.getElementById("editModal");

editButtons.forEach(btn => {
    btn.addEventListener("click", () => {

        editModal.classList.add("show");

      const rule = window.rule_sensor;

document.getElementById("ph_baik_min").value =
    rule.ph_min_good;

document.getElementById("ph_baik_max").value =
    rule.ph_max_good;

document.getElementById("ph_warn_min").value =
    rule.ph_min_warning;

document.getElementById("ph_warn_max").value =
    rule.ph_max_warning;

document.getElementById("ph_bahaya").value =
    `${rule.ph_danger_low} atau ${rule.ph_danger_high}`;

document.getElementById("tur_baik_min").value =
    rule.turbidity_min_good;

document.getElementById("tur_baik_max").value =
    rule.turbidity_max_good;

document.getElementById("tur_warn_min").value =
    rule.turbidity_min_warning;

document.getElementById("tur_warn_max").value =
    rule.turbidity_max_warning;

document.getElementById("tur_bahaya").value =
    `${rule.turbidity_danger_low} atau ${rule.turbidity_danger_high}`;
    });
});

// =========================
// CLOSE MODAL
// =========================
window.closeEdit = function () {
    editModal.classList.remove("show");
};

// =========================
// SAVE RULE (FIX: REMOVE DUPLICATE EVENT)
// =========================
window.addEventListener("load", () => {
    const btn = document.getElementById("saveRuleBtn");

    if (!btn) return;

    btn.addEventListener("click", () => {
        console.log("CLICK SAVE RULE OK");
    });
});
const saveRuleBtn =
    document.getElementById("saveRuleBtn");

if (saveRuleBtn) {

    saveRuleBtn.addEventListener("click", async () => {

        try {

            const phBahaya =
                document.getElementById("ph_bahaya")
                .value
                .split("atau");

            const turBahaya =
                document.getElementById("tur_bahaya")
                .value
                .split("atau");

            const res = await fetch("/pengaturan/rule", {

                method: "POST",

                headers: {
                    "Content-Type": "application/json",

                    "X-CSRF-TOKEN":
                        document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content
                },

                body: JSON.stringify({

                    ph_min_good:
                        document.getElementById("ph_baik_min").value,

                    ph_max_good:
                        document.getElementById("ph_baik_max").value,

                    ph_min_warning:
                        document.getElementById("ph_warn_min").value,

                    ph_max_warning:
                        document.getElementById("ph_warn_max").value,

                    ph_danger_low:
                        phBahaya[0]?.replace("<", "").trim() || 6,

                    ph_danger_high:
                        phBahaya[1]?.replace(">", "").trim() || 9,

                    turbidity_min_good:
                        document.getElementById("tur_baik_min").value,

                    turbidity_max_good:
                        document.getElementById("tur_baik_max").value,

                    turbidity_min_warning:
                        document.getElementById("tur_warn_min").value,

                    turbidity_max_warning:
                        document.getElementById("tur_warn_max").value,

                    turbidity_danger_low:
                        turBahaya[0]?.trim() || 10,

                    turbidity_danger_high:
                        turBahaya[1]?.trim() || 15
                })
            });

            const data = await res.json();

            if (!res.ok) {
                showToast(data.message || "Gagal", "error");
                return;
            }

            showToast("Rule berhasil disimpan");

            closeEdit();

            location.reload();

        } catch (err) {

            console.error(err);

            showToast("Server error", "error");
        }
    });
}
console.log("saveRuleBtn =", document.getElementById("saveRuleBtn"));
// =========================
// REALTIME SENSOR
// =========================

async function loadRealtimeSensor() {

    try {

        const res = await fetch("/sensor/realtime");
        const data = await res.json();

        console.log(data);

        const statusBox =
            document.getElementById("statusBox");

        if (statusBox) {

            statusBox.innerHTML = `
                <b>pH:</b> ${data.ph}
                (${data.ph_status})
                <br>

                <b>Turbidity:</b>
                ${data.turbidity}
                (${data.turbidity_status})
            `;
        }

    } catch (err) {

        console.error(err);
    }
}

// load pertama
loadRealtimeSensor();

// realtime tiap 3 detik
setInterval(loadRealtimeSensor, 3000);