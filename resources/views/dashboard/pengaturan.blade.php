@extends('footbar.utama')

@section('title', 'Halaman Pengaturan')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('css/pengaturan.css') }}">

<div class="container">

    <!-- HEADER -->
    <div class="header">
        <div class="header-left">
            <div class="icon">⚙️</div>
            <div>
                <h1>Pengaturan Rekomendasi Pakan</h1>
                <small>Manajemen otomatis berbasis sensor tambak</small>
            </div>
        </div>

        <div class="header-right">
            <button class="btn-help">❓ Bantuan</button>
        </div>
    </div>

    <!-- GRID -->
    <div class="grid">

        <!-- VARIABEL SENSOR -->
        <div class="card">
            <h2>Variabel Sensor</h2>

            <table>
                <thead>
                    <tr>
                        <th>Variabel</th>
                        <th>Kondisi</th>
                        <th>Rentang</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    <tr>
                        <td>💧 pH</td>
                        <td><span class="badge good">Baik</span></td>
                        <td id="ph_baik_text">7.5 - 8.5</td>
                        <td><button type="button" class="btn-edit open-edit">✏️</button></td>
                    </tr>

                    <tr>
                        <td>💧 pH</td>
                        <td><span class="badge warning">Peringatan</span></td>
                        <td id="ph_warn_text">7.0 - 7.4</td>
                    </tr>

                    <tr>
                        <td>💧 pH</td>
                        <td><span class="badge danger">Bahaya</span></td>
                        <td id="ph_bahaya_text">< 7 atau > 8.5</td>
                    </tr>

                    <tr>
                        <td>⚪ Turbidity</td>
                        <td><span class="badge good">Baik</span></td>
                        <td id="tur_baik_text">10 - 50</td>
                    </tr>

                    <tr>
                        <td>⚪ Turbidity</td>
                        <td><span class="badge warning">Peringatan</span></td>
                        <td id="tur_warn_text">51 - 70</td>
                    </tr>

                    <tr>
                        <td>⚪ Turbidity</td>
                        <td><span class="badge danger">Bahaya</span></td>
                        <td id="tur_bahaya_text">> 70</td>
                    </tr>

                </tbody>
            </table>
        </div>

        <!-- RULE ENGINE -->
        <div class="card">
            <h2>Rule Engine</h2>

            <div class="rule-box">
                <ul>
                    <li>✔ Kondisi baik → pakan optimal</li>
                    <li>⚠ Kondisi sedang → pakan dikurangi</li>
                    <li>❌ Kondisi buruk → pakan minimal</li>
                </ul>
            </div>

            <div class="rule-status">
                <span class="dot active"></span> Sistem Aktif
            </div>
        </div>

    </div>

    <!-- OUTPUT -->
    <div class="card output premium-card">

        <div class="output-left">

            <h2>⚡ Pengingat Anda</h2>

            <div class="form-group">
                <label>👨‍🌾 Nama Penjaga</label>
                <div id="penjagaContainer"></div>
                <button type="button" id="addPenjaga" class="btn-add">+ Tambah Penjaga</button>
            </div>

            <div class="form-group">
                <label>⏰ Waktu Pakan</label>
                <div id="waktuContainer"></div>
                <button type="button" id="addWaktu" class="btn-add">+ Tambah Waktu</button>
            </div>

            <div class="form-group">
                <label>📅 Tanggal</label>
                <input type="date" id="tanggalInput" class="form-control">
            </div>

        </div>

        <div class="output-right premium-result">

            <h3>📊 Monitoring Tambak</h3>

            <div id="statusBox" class="status-box">Status: -</div>

            <div class="result-box">
                <h1 id="beratHighlight">0 gram</h1>
                <p>Rekomendasi Pakan</p>
            </div>

            <div class="info-preview">
                <p><b>Penjaga:</b> <span id="penjagaNow">-</span></p>
                <p><b>Waktu:</b> <span id="waktuNow">-</span></p>
                <p><b>Tanggal:</b> <span id="tanggalNow">-</span></p>
            </div>

        </div>

    </div>

    <!-- ACTION -->
    <div class="actions">
        <button class="btn-reset">Reset</button>
        <button id="btnSimpan" class="btn-save">Simpan Pengaturan</button>
    </div>

</div>

<!-- ================= MODAL ================= -->
<div id="editModal" class="modal">
    <div class="modal-premium">

        <div class="modal-header">
            <h2>⚙️ Edit Rule Engine</h2>
            <span onclick="closeEdit()" class="close-btn">✖</span>
        </div>

        <div class="modal-body">

            <!-- PH -->
            <div class="rule-group">
                <h3>💧 pH</h3>

                <div class="range-grid">
                    <div>
                        <label>Baik</label>
                        <div class="range-input">
                            <input type="number" id="ph_baik_min">
                            <span>-</span>
                            <input type="number" id="ph_baik_max">
                        </div>
                    </div>

                    <div>
                        <label>Peringatan</label>
                        <div class="range-input">
                            <input type="number" id="ph_warn_min">
                            <span>-</span>
                            <input type="number" id="ph_warn_max">
                        </div>
                    </div>

                    <div>
                        <label>Bahaya</label>
                        <input type="text" id="ph_bahaya">
                    </div>
                </div>
            </div>

            <!-- TURBIDITY -->
            <div class="rule-group">
                <h3>⚪ Turbidity</h3>

                <div class="range-grid">
                    <div>
                        <label>Baik</label>
                        <div class="range-input">
                            <input type="number" id="tur_baik_min">
                            <span>-</span>
                            <input type="number" id="tur_baik_max">
                        </div>
                    </div>

                    <div>
                        <label>Peringatan</label>
                        <div class="range-input">
                            <input type="number" id="tur_warn_min">
                            <span>-</span>
                            <input type="number" id="tur_warn_max">
                        </div>
                    </div>

                    <div>
                        <label>Bahaya</label>
                        <input type="text" id="tur_bahaya">
                    </div>
                </div>
            </div>

        </div>

        <!-- 🔥 FIX: BUTTON SIMPAN YANG HILANG -->
        <div class="modal-footer">
            <button class="btn-reset" onclick="closeEdit()">Batal</button>
            <button id="saveRuleBtn" class="btn-save">💾 Simpan Rule</button>
        </div>

    </div>
</div>

<!-- ================= TOAST ================= -->
<div id="toast" class="toast">
    <div class="toast-content">
        <span id="toastIcon">✔️</span>
        <span id="toastMessage">Berhasil disimpan</span>
    </div>
</div>

<!-- ================= SCRIPT ================= -->
<script>
document.getElementById("btnSimpan").addEventListener("click", function () {

    let penjaga = [];
    document.querySelectorAll("#penjagaContainer input").forEach(el => {
        penjaga.push(el.value);
    });

    let waktu = [];
    document.querySelectorAll("#waktuContainer input").forEach(el => {
        waktu.push(el.value);
    });

    fetch("/pengaturan/simpan", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            penjaga,
            waktu,
            tanggal: document.getElementById("tanggalInput").value
        })
    })
    .then(res => res.json())
    .then(() => showToast("Berhasil disimpan!", "success"))
    .catch(() => showToast("Gagal menyimpan!", "error"));
});

// TOAST FUNCTION
function showToast(msg, type) {
    let t = document.getElementById("toast");
    document.getElementById("toastMessage").innerText = msg;

    t.className = "toast show " + type;

    setTimeout(() => t.classList.remove("show"), 3000);
}
</script>
<script>
function saveRule() {

    let rule_sensor = {
        ph: {
            low: [0, parseFloat(document.getElementById("ph_min").value)],
            normal: [
                parseFloat(document.getElementById("ph_min").value),
                parseFloat(document.getElementById("ph_max").value)
            ],
            high: [parseFloat(document.getElementById("ph_max").value), 14]
        },
        turbidity: {
            low: [0, parseFloat(document.getElementById("tur_min").value)],
            normal: [
                parseFloat(document.getElementById("tur_min").value),
                parseFloat(document.getElementById("tur_max").value)
            ],
            high: [parseFloat(document.getElementById("tur_max").value), 50]
        }
    };

    fetch("/pengaturan/store", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            rule_sensor: rule_sensor,
            pengingat: "auto"
        })
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
    })
    .catch(err => console.log(err));
}
</script>
<script>
let rule = @json($rule);
</script>
<script src="{{ asset('js/pengaturan.js') }}"></script>

@endsection