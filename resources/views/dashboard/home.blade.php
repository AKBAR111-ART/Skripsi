@extends('footbar.utama')

@section('title', 'Dashboard Tambak')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/home.css') }}">
@endpush

@section('content')

<div class="dashboard">

    <!-- TOP BAR -->
    <div class="top-cards">
        <div class="top-card green">📦 Pakan Hari Ini: <b id="topFeed">-</b></div>
        <div class="top-card green">🛡️ Kondisi Air: <b id="topWater">-</b></div>
        <div class="top-card red">⚠ Status: <b id="topStatus">-</b></div>
    </div>

    <!-- MAIN CARD -->
    <div class="main-grid">

        <!-- KIRI -->
        <div class="card">
            <h3>Informasi Kualitas Air (Real-time)</h3>

            <div class="gauge-wrap">

                <!-- PH -->
                 <div class="gauge">
                    <h5>pH Air</h5>
                    <canvas id="phGauge"></canvas>
                    <div class="gauge-value" id="phText">0</div>
                    <span class="badge green" id="phStatus">Normal</span>
                </div>


                <!-- TURBIDITY -->
                <div class="gauge">
                   <h5>Turbidity</h5>
                    <canvas id="turbGauge"></canvas>
                    <div class="gauge-value" id="turbText">0 NTU</div>
                    <span class="badge yellow" id="turbStatus">Sedang</span>
                </div>

            </div>
        </div>

        <!-- TENGAH -->
        <div class="card">
            <h3>Rekomendasi Pakan</h3>

            <ul class="list">
                <li>✔ Frekuensi: <b>4x sehari</b></li>
                <li>✔ Waktu: <b>07.00, 11.00, 19.00</b></li>
            </ul>

            <!-- 🔥 BOX HASIL -->
            <div class="feed-box">
                <div class="feed-label">Estimasi Pakan</div>
                <div class="feed-value" id="feedValue">0 Kg</div>
                <div class="feed-note">Berdasarkan kondisi air</div>
            </div>
        </div>

        <!-- KANAN -->
        <div class="card">
            <h3>Status Kondisi Tambak</h3>

            <div class="status-box yellow" id="pondCondition">-</div>
            <div class="status-box blue" id="pondCause">-</div>

            <p class="action" id="pondAction">
                -
            </p>
        </div>

    </div>

    <!-- ALERT -->
    <div class="alert-box" id="alertBox">
        -
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