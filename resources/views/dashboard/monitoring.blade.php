@extends('footbar.utama')

@section('title', 'Detail Monitoring')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/monitoring.css') }}">
@endpush

@section('content')

<div class="monitoring">

    <!-- HEADER -->
    <div class="header">
        <h1>Detail Monitoring Tambak </h1>
    </div>

    <!-- =========================
         TOP GRID
    ========================== -->
    <div class="grid-top">

        <!-- LEFT CARD -->
        <div class="card glass">

            <h3>Grafik Monitoring pH - Pond A</h3>

            <div class="gauge-box">

                <!-- PH -->
                <div class="gauge-item">
                    <canvas id="phGauge"></canvas>
                    <div class="gauge-info">
                        <h4>pH Air</h4>
                        <div class="value" id="phValue">7.8</div>
                        <span class="badge green">Normal</span>
                    </div>
                </div>

                <!-- TURBIDITY -->
                <div class="gauge-item">
                    <canvas id="turbGauge"></canvas>
                    <div class="gauge-info">
                        <h4>Turbidity (NTU)</h4>
                        <div class="value" id="turbValue">30</div>
                        <span class="badge yellow">Sedang</span>
                    </div>
                </div>

            </div>

            <div class="avg">Rata-rata pH: 7.58</div>

        </div>

        <!-- RIGHT CARD (🔥 ANIMASI MASUK) -->
<div class="card rekom shrimp-card">

    <!-- HEADER -->
    <div class="shrimp-header">
        ⚠ AIR KERUH
        <span>Pakan Dikurangi 30%</span>
    </div>

    <!-- 🔥 AREA ANIMASI + INFO DIGABUNG -->
    <div class="shrimp-animation" id="shrimpArea">

        <div class="water"></div>

        <!-- UDANG -->
        <div class="shrimp shrimp-1"></div>
        <div class="shrimp shrimp-2"></div>
        <div class="shrimp shrimp-3"></div>

        <!-- 🔥 INFO DI DALAM AIR -->
        <div class="shrimp-overlay">
            <h4>Jumlah Pakan Hari Ini</h4>
            <h1>350 gram</h1>

            <p>Frekuensi</p>
            <b>4x sehari</b>
        </div>

    </div>

</div>

    </div>

    <!-- =========================
         BOTTOM GRID
    ========================== -->
    <div class="grid-bottom">

        <!-- TABLE MONITORING -->
        <div class="card glass">
            <h3>Data Monitoring</h3>

            <table>
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>pH</th>
                        <th>Kekeruhan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="tableMonitoring"></tbody>
            </table>
        </div>

        <!-- TABLE PAKAN -->
        <div class="card glass">
            <h3>Detail Pakan</h3>

            <table>
                <thead>
                    <tr>
                        <th>Pukul</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="tableFeed"></tbody>
            </table>

            <div class="ok">✔ Pola sesuai rekomendasi</div>
        </div>

    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/monitoring.js') }}"></script>
@endpush