@extends('footbar.utama')

@section('title', 'Dashboard Tambak')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/home.css') }}">
@endpush

@section('content')

<div class="dashboard">

    <!-- TOP BAR -->
    <div class="top-cards">
        <div class="top-card green">📦 Pakan Hari Ini: <b>500 gram</b></div>
        <div class="top-card green">🛡️ Kondisi Air: <b>Normal</b></div>
        <div class="top-card red">⚠ Status: <b>Aman</b></div>
    </div>

    <!-- MAIN CARD -->
    <div class="main-grid">

        <!-- KIRI -->
        <div class="card">
            <h3>Informasi Kualitas Air (Real-time)</h3>

            <div class="gauge-wrap">
                <div class="gauge">
                    <canvas id="phGauge"></canvas>
                    <div class="gauge-value" id="phText">7.8</div>
                    <span class="badge green">Normal</span>
                </div>

                <div class="gauge">
                    <canvas id="turbGauge"></canvas>
                    <div class="gauge-value" id="turbText">30</div>
                    <span class="badge yellow">Sedang</span>
                </div>
            </div>
        </div>

        <!-- TENGAH -->
        <div class="card">
            <h3>Rekomendasi Pakan</h3>
            <ul class="list">
                <li>✔ Jumlah Pakan: <b>500 gram / hari</b></li>
                <li>✔ Frekuensi: <b>4x sehari</b></li>
                <li>✔ Waktu: <b>07.00, 11.00, 19.00</b></li>
            </ul>
        </div>

        <!-- KANAN -->
        <div class="card">
            <h3>Status Kondisi Tambak</h3>

            <div class="status-box yellow">Kondisi: Kurang Baik</div>
            <div class="status-box blue">Penyebab: Air Terlalu Keruh</div>

            <p class="action">
                Kurangi Pakan & Tambah Air Baru
            </p>
        </div>

    </div>

    <!-- ALERT -->
    <div class="alert-box">
        ⚠ ALERT: Air Keruh! Kurangi Pakan!
    </div>

    <!-- GRAFIK -->
    <div class="chart-grid">

        <div class="card">
            <h4>Grafik pH</h4>
            <canvas id="chartPh"></canvas>
        </div>

        <div class="card">
            <h4>Grafik Kekeruhan</h4>
            <canvas id="chartTurb"></canvas>
        </div>

        <div class="card">
            <h4>Grafik Pakan Mingguan</h4>
            <canvas id="chartFeed"></canvas>
        </div>

    </div>

    <!-- BOTTOM -->
    <div class="bottom-grid">

        <div class="card">
            <h4>Rule yang Digunakan</h4>
            <p>✔ IF pH < 6.5 AND turbidity > 50 → kurangi pakan</p>
            <p>✔ IF normal → pakan optimal</p>
        </div>

        <div class="card">
            <h4>Data Tambahan</h4>
            <p>🦐 Umur: 6 Minggu</p>
            <p>⚖ Berat: 7.5 gram</p>
            <p>📦 Biomassa: 120 kg</p>
        </div>

    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/home.js') }}"></script>
@endpush