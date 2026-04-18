@extends('footbar.utama')

@section('title', 'Halaman Home')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/home.css') }}">
@endpush

@section('content')

<div class="dashboard-container">

    <!-- KIRI (GRAFIK - KOSONG DULU) -->
    <div class="left-panel">
        <!-- nanti isi chart di sini -->
    </div>

    <!-- KANAN (SEMUA CARD) -->
    <div class="right-panel">

        <div class="big-card">

            <!-- CARD ATAS -->
            <div class="monitoring-container">
                
                <div class="card-monitor ph normal">
                    <div id="phIcon" class="status-icon"></div>
                    <h3>💧 pH Air</h3>
                    <div class="value" id="phValue">7.0</div>
                    <div class="status normal" id="phStatus">Normal</div>
                </div>

                <div class="card-monitor turbidity normal">
                    <div id="turbidityIcon" class="status-icon"></div>
                    <h3>🌊 Turbidity</h3>
                    <div class="value" id="turbidityValue">20 NTU</div>
                    <div class="status normal" id="turbidityStatus">Jernih</div>
                </div>

            </div>

            <!-- REKOMENDASI -->
            <div class="rekomendasi-card normal">
    <h3>🍤 Rekomendasi Pakan</h3>
    <p id="rekomendasiText">Menunggu data...</p>

    <!-- 🔥 BOX HASIL KALKULASI -->
    <div class="feed-box">
        <div class="feed-label">Estimasi Pakan</div>
        <div class="feed-value">2.5 <span>Kg</span></div>
        <div class="feed-note">Berdasarkan kondisi air saat ini</div>
    </div>
</div>

        </div>

    </div>

</div>

@endsection

@push('scripts')
<script src="{{ asset('js/home.js') }}"></script>
@endpush