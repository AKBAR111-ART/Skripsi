@extends('footbar.utama')

@section('title', 'Profil Tambak')

@section('content')

<link rel="stylesheet" href="{{ asset('css/profile.css') }}">

<div class="profile-container">

    <!-- HEADER -->
    <div class="profile-header">
        <div class="left">
           <div class="profile-icon">
    <img src="{{ asset('images/profile1.jpg') }}" alt="">
</div>
            <div class="header-tex">
                <h2>Profil Tambak Udang</h2>
                <p>Informasi dan kondisi umum tambak udang Anda.</p>
            </div>
        </div>

        <button class="edit-btn">
            ✏️ Edit Profil
        </button>
    </div>

    <!-- CARD UTAMA -->
    <div class="main-card">

        <!-- IMAGE -->
        <div class="image-box">
            <img src="{{ asset('images/tambak4.jpeg') }}" alt="Tambak">
        </div>

        <!-- INFO -->
        <div class="info-box">
            <h3>Informasi Tambak</h3>

            <div class="info-item">
                <span>Nama Tambak</span>
                <b>Tambak Udang Sejahtera</b>
            </div>

            <div class="info-item">
                <span>Lokasi</span>
                <b>Kab. Pinrang, Sulawesi Selatan</b>
            </div>

            <div class="info-item">
                <span>Luas Tambak</span>
                <b>2.5 Hektar</b>
            </div>

            <div class="info-item">
                <span>Tipe Tambak</span>
                <b>Semi Intensif</b>
            </div>

            <div class="info-item">
                <span>Tanggal Dibuat</span>
                <b>10 Mei 2024</b>
            </div>
        </div>

        <!-- RINGKASAN -->
        <div class="summary-box">
            <h3>Ringkasan</h3>

            <div class="summary-card">
                <div class="row">
                    <div class="icon">💧</div>
                    <div>
                        <p>pH Air</p>
                        <b>7.82</b>
                    </div>
                    <span class="status normal">Normal</span>
                </div>

                <div class="row">
                    <div class="icon">⚪</div>
                    <div>
                        <p>Turbidity</p>
                        <b>35 NTU</b>
                    </div>
                    <span class="status normal">Normal</span>
                </div>
            </div>

        </div>

    </div>

    <!-- PARAMETER -->
    <div class="bottom-grid">

        <div class="card">
            <h3>Parameter yang Digunakan</h3>
            <p class="desc">Parameter ini digunakan sebagai input dalam rekomendasi pakan.</p>

            <div class="param-item">
                <div class="left">
                    💧
                    <div>
                        <b>pH Air</b>
                        <p>Tingkat keasaman atau kebasaan air tambak.</p>
                    </div>
                </div>
                <span class="badge">Aktif</span>
            </div>

            <div class="param-item">
                <div class="left">
                    ⚪
                    <div>
                        <b>Turbidity</b>
                        <p>Kekeruhan air tambak.</p>
                    </div>
                </div>
                <span class="badge">Aktif</span>
            </div>
        </div>

        <!-- MASA BUDIDAYA -->
        <div class="card">
            <h3>Masa Budidaya</h3>
            <p class="desc">Informasi umur atau masa budidaya saat ini.</p>

            <div class="budidaya-box">
                <div class="top">
                    <div class="icon">📅</div>
                    <div>
                        <p>Umur Budidaya Saat Ini</p>
                        <h2>68 Hari</h2>
                        <small>Mulai tebar: 10 Maret 2024</small>
                    </div>
                </div>

                <div class="progress">
                    <div class="bar"></div>
                </div>

                <div class="bottom">
                    <span>68 / 120 Hari</span>
                    <span>Estimasi panen: 08 Juli 2024</span>
                </div>
            </div>
        </div>

    </div>

    <!-- CATATAN -->
    <div class="note">
        <b>Catatan</b>
        <p>Pastikan sensor pH dan Turbidity terpasang dengan baik dan data selalu diperbarui untuk mendapatkan rekomendasi pakan yang akurat.</p>
    </div>

</div>

<script src="{{ asset('js/profile.js') }}"></script>

@endsection