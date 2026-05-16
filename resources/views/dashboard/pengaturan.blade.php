@extends('footbar.utama')

@php

$rule = $rule ?? [

    'ph_min_good' => 7.5,
    'ph_max_good' => 8.5,

    'ph_min_warning' => 7.0,
    'ph_max_warning' => 7.4,

    'ph_danger_low' => 7.0,
    'ph_danger_high' => 8.5,

    'turbidity_min_good' => 10,
    'turbidity_max_good' => 50,

    'turbidity_min_warning' => 51,
    'turbidity_max_warning' => 70,

    'turbidity_danger_low' => 10,
    'turbidity_danger_high' => 15,
];

@endphp

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
            <button type="button" class="btn-help">
                ❓ Bantuan
            </button>
        </div>

    </div>

    <!-- GRID -->
    <div class="grid">

        <!-- SENSOR -->
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

                    <!-- PH -->
                    <tr>
                        <td>💧 pH</td>

                        <td>
                            <span class="badge good">
                                Baik
                            </span>
                        </td>

                        <td id="ph_baik_text">
                            {{ $rule['ph_min_good'] ?? '7.5' }}
                            -
                            {{ $rule['ph_max_good'] ?? '8.5' }}
                        </td>

                        <td>
                            <button
                                type="button"
                                class="btn-edit open-edit">

                                ✏️
                            </button>
                        </td>
                    </tr>

                    <tr>
                        <td>💧 pH</td>

                        <td>
                            <span class="badge warning">
                                Peringatan
                            </span>
                        </td>

                        <td id="ph_warn_text">
                            {{ $rule['ph_min_warning'] ?? '7.0' }}
                            -
                            {{ $rule['ph_max_warning'] ?? '7.4' }}
                        </td>
                    </tr>

                    <tr>
                        <td>💧 pH</td>

                        <td>
                            <span class="badge danger">
                                Bahaya
                            </span>
                        </td>

                        <td id="ph_bahaya_text">
                            < {{ $rule['ph_danger_low'] ?? '6.0' }}
                            atau
                            > {{ $rule['ph_danger_high'] ?? '9.0' }}
                        </td>
                    </tr>

                    <!-- TURBIDITY -->
                    <tr>
                        <td>⚪ Turbidity</td>

                        <td>
                            <span class="badge good">
                                Baik
                            </span>
                        </td>

                        <td id="tur_baik_text">
                            {{ $rule['turbidity_min_good'] ?? '10' }}
                            -
                            {{ $rule['turbidity_max_good'] ?? '50' }}
                        </td>
                    </tr>

                    <tr>
                        <td>⚪ Turbidity</td>

                        <td>
                            <span class="badge warning">
                                Peringatan
                            </span>
                        </td>

                        <td id="tur_warn_text">
                            {{ $rule['turbidity_min_warning'] ?? '51' }}
                            -
                            {{ $rule['turbidity_max_warning'] ?? '70' }}
                        </td>
                    </tr>

                    <tr>
                        <td>⚪ Turbidity</td>

                        <td>
                            <span class="badge danger">
                                Bahaya
                            </span>
                        </td>

                        <td id="tur_bahaya_text">
                            < {{ $rule['turbidity_danger_low'] ?? '10' }}
                            atau
                            > {{ $rule['turbidity_danger_high'] ?? '15' }}
                        </td>
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
                <span class="dot active"></span>
                Sistem Aktif
            </div>

        </div>

    </div>

    <!-- OUTPUT -->
    <div class="card output premium-card">

        <!-- LEFT -->
        <div class="output-left">

            <h2>⚡ Pengingat Anda</h2>

            <!-- PENJAGA -->
            <div class="form-group">

                <label>👨‍🌾 Nama Penjaga</label>

                <div id="penjagaContainer"></div>

                <button
                    type="button"
                    id="addPenjaga"
                    class="btn-add">

                    + Tambah Penjaga
                </button>

            </div>

            <!-- NOMOR WA -->
            <div class="form-group">

                <label>📱 Nomor WhatsApp</label>

                <div id="waContainer"></div>

                <button
                    type="button"
                    id="addWa"
                    class="btn-add">

                    + Tambah Nomor WA
                </button>

            </div>

            <!-- WAKTU -->
            <div class="form-group">

                <label>⏰ Waktu Pakan</label>

                <div id="waktuContainer"></div>

                <button
                    type="button"
                    id="addWaktu"
                    class="btn-add">

                    + Tambah Waktu
                </button>

            </div>

            <!-- TANGGAL -->
            <div class="form-group">

                <label>📅 Tanggal</label>

                <input
                    type="date"
                    id="tanggalInput"
                    class="form-control"
                    value="{{ $pengaturan->tanggal ?? '' }}"
                >

            </div>

        </div>

        <!-- RIGHT -->
        <div class="output-right premium-result">

            <h3>📊 Monitoring Tambak</h3>

            <!-- TEMPLATE PESAN -->
            <div class="form-group" style="margin-top:20px;">

                <label>💬 Template Pesan WhatsApp</label>

                <textarea
                    id="templatePesan"
                    name="pengingat[template_pesan]"
                    class="form-control"
                    rows="6"
                ></textarea>

                <small>
                    Gunakan:
                    @{{penjaga}}
                    @{{waktu}}
                    @{{tanggal}}
                </small>

            </div>

            <div id="statusBox" class="status-box">
                Status: -
            </div>

            <div class="result-box">
                <h1 id="beratHighlight">0 gram</h1>
                <p>Rekomendasi Pakan</p>
            </div>

            <div class="info-preview">

                <p>
                    <b>Penjaga:</b>
                    {{ is_array($pengaturan->penjaga ?? null)
                        ? implode(', ', $pengaturan->penjaga)
                        : (is_string($pengaturan->penjaga ?? null)
                            ? implode(', ', json_decode($pengaturan->penjaga, true) ?? [])
                            : '-') }}
                </p>

                <p>
                    <b>WA:</b>
                    {{ is_array($pengaturan->nomor_wa ?? null)
                        ? implode(', ', $pengaturan->nomor_wa)
                        : (is_string($pengaturan->nomor_wa ?? null)
                            ? implode(', ', json_decode($pengaturan->nomor_wa, true) ?? [])
                            : '-') }}
                </p>

                <p>
                    <b>Waktu:</b>
                    {{ is_array($pengaturan->waktu ?? null)
                        ? implode(', ', $pengaturan->waktu)
                        : (is_string($pengaturan->waktu ?? null)
                            ? implode(', ', json_decode($pengaturan->waktu, true) ?? [])
                            : '-') }}
                </p>

                <p>
                    <b>Tanggal:</b>
                    <span id="tanggalNow">
                        {{ $pengaturan->tanggal ?? '-' }}
                    </span>
                </p>

            </div>

        </div>

    </div>

    <!-- ACTION -->
    <div class="actions">

        <button
            type="button"
            class="btn-reset">

            Reset
        </button>

        <button
            type="button"
            id="btnSimpan"
            class="btn-save">

            Simpan Pengaturan
        </button>

    </div>

</div>

<!-- ================= MODAL ================= -->

<div id="editModal" class="modal">

    <div class="modal-premium">

        <!-- HEADER -->
        <div class="modal-header">

            <h2>⚙️ Edit Rule Engine</h2>

            <span
                onclick="closeEdit()"
                class="close-btn">

                ✖
            </span>

        </div>

        <!-- BODY -->
        <div class="modal-body">

            <!-- PH -->
            <div class="rule-group">

                <h3>💧 pH</h3>

                <div class="range-grid">

                    <div>

                        <label>Baik</label>

                        <div class="range-input">

                            <input
                                type="number"
                                step="0.1"
                                id="ph_baik_min"
                                value="{{ $rule['ph_min_good'] ?? '7.5' }}">

                            <span>-</span>

                            <input
                                type="number"
                                step="0.1"
                                id="ph_baik_max"
                                value="{{ $rule['ph_max_good'] ?? '8.5' }}">

                        </div>

                    </div>

                    <div>

                        <label>Peringatan</label>

                        <div class="range-input">

                            <input
                                type="number"
                                step="0.1"
                                id="ph_warn_min"
                                value="{{ $rule['ph_min_warning'] ?? '7.0' }}">

                            <span>-</span>

                            <input
                                type="number"
                                step="0.1"
                                id="ph_warn_max"
                                value="{{ $rule['ph_max_warning'] ?? '7.4' }}">

                        </div>

                    </div>

                    <div>

                        <label>Bahaya</label>

                        <input
                            type="text"
                            id="ph_bahaya"
                            value="< {{ $rule['ph_danger_low'] ?? '6.0' }} atau > {{ $rule['ph_danger_high'] ?? '9.0' }}">

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

                            <input
                                type="number"
                                id="tur_baik_min"
                                value="{{ $rule['turbidity_min_good'] ?? '10' }}">

                            <span>-</span>

                            <input
                                type="number"
                                id="tur_baik_max"
                                value="{{ $rule['turbidity_max_good'] ?? '50' }}">

                        </div>

                    </div>

                    <div>

                        <label>Peringatan</label>

                        <div class="range-input">

                            <input
                                type="number"
                                id="tur_warn_min"
                                value="{{ $rule['turbidity_min_warning'] ?? '51' }}">

                            <span>-</span>

                            <input
                                type="number"
                                id="tur_warn_max"
                                value="{{ $rule['turbidity_max_warning'] ?? '70' }}">

                        </div>

                    </div>

                    <div>

                        <label>Bahaya</label>

                        <input
                            type="text"
                            id="tur_bahaya"
                            value="< {{ $rule['turbidity_danger_low'] ?? '10' }} atau > {{ $rule['turbidity_danger_high'] ?? '15' }}">

                    </div>

                </div>

            </div>

        </div>

        <!-- FOOTER -->
        <div class="modal-footer">

            <button
                type="button"
                class="btn-reset"
                onclick="closeEdit()">

                Batal
            </button>

            <button id="saveRuleBtn" class="btn-save">
                💾 Simpan Rule
            </button>

        </div>

    </div>

</div>

<!-- ================= TOAST ================= -->

<div id="toast" class="toast">

    <div class="toast-content">

        <span id="toastIcon">
            ✔️
        </span>

        <span id="toastMessage">
            Berhasil disimpan
        </span>

    </div>

</div>

<!-- ================= DATA ================= -->

<script>

window.rule_sensor = @json($rule);
window.pengaturanData = @json($pengaturan);
window.rule = @json($rule);

</script>

<!-- ================= JS ================= -->

<script src="{{ asset('js/pengaturan.js') }}"></script>

@endsection