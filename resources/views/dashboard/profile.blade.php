@extends('footbar.utama')

@section('title', 'Profil Tambak')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">

@php
    use Carbon\Carbon;

    $start = ($profile && $profile->tanggal_mulai_budidaya)
        ? Carbon::parse($profile->tanggal_mulai_budidaya)
        : null;

    $today = Carbon::now();

    $days = $start ? max(0, (int) $start->diffInDays($today)) : 0;

    $total = 90;

    $percent = $start ? min(($days / $total) * 100, 100) : 0;

    $estimasiPanen = $start ? $start->copy()->addDays(90)->format('d M Y') : '-';
@endphp

<div class="profile-container">

    <!-- HEADER -->
    <div class="profile-header">
        <div class="left">
            <div class="profile-icon">
                <img src="{{ asset('images/profile1.jpg') }}" alt="">
            </div>

            <div class="header-tex">
                <h2>Profil Tambak</h2>
                <p>Informasi dan kondisi umum tambak udang Anda.</p>
            </div>
        </div>

        <button class="edit-btn" onclick="openEditModal()">
            ✏️ Edit Profil
        </button>
    </div>

    <!-- CARD UTAMA -->
    <div class="main-card">

        <div class="image-box">
            <img src="{{ asset('images/tambak4.jpeg') }}" alt="Tambak">
        </div>

        <div class="info-box">
            <h3>Informasi Tambak</h3>

            <div class="info-item">
                <span>Nama Tambak</span>
                <b>{{ $profile->nama_tambak ?? '-' }}</b>
            </div>

            <div class="info-item">
                <span>Lokasi</span>
                <b>{{ $profile->lokasi ?? '-' }}</b>
            </div>

            <div class="info-item">
                <span>Luas Tambak</span>
                <b>{{ $profile->luas ?? 0 }} m²</b>
            </div>

            <div class="info-item">
                <span>Tipe Tambak</span>
                <b>{{ $profile->tipe_tambak ?? '-' }}</b>
            </div>

            <div class="info-item">
                <span>Biomassa Udang</span>
                <b>{{ $profile->biomassa_udang ?? 0 }} gram</b>
            </div>

            <div class="info-item">
                <span>Tanggal Dibuat</span>
                <b>{{ $profile->tanggal_dibuat ?? '-' }}</b>
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

            <div class="param-item">
                <div class="left">
                    💧 <div><b>pH Air</b><p>Tingkat keasaman air tambak.</p></div>
                </div>
                <button onclick="kalibrasi('pH')">Kalibrasi</button>
            </div>

            <div class="param-item">
                <div class="left">
                    ⚪ <div><b>Turbidity</b><p>Kekeruhan air tambak.</p></div>
                </div>
                <button onclick="kalibrasi('Turbidity')">Kalibrasi</button>
            </div>

            <div class="param-item">
                <div class="left">
                    🦐 <div><b>Biomassa Udang</b><p>Estimasi berat total udang.</p></div>
                </div>
                <button onclick="openBiomassa()">Edit</button>
            </div>

        </div>

        <!-- ================= BUDIDAYA ================= -->
        <div class="card">
            <h3>Masa Budidaya</h3>

            @if(!$profile || !$start)

                <button class="edit-btn" onclick="openBudidayaModal()">
                    📅 Mulai Budidaya
                </button>

            @else

                <div class="budidaya-box">

                    <div class="top">
                        <div class="icon">📅</div>
                        <div>
                            <p>Umur Budidaya</p>
                            <h2>{{ $days }} Hari</h2>
                            <small>Mulai: {{ $profile->tanggal_mulai_budidaya }}</small>
                        </div>
                    </div>

                    <div class="progress">
                        <div class="bar" style="width: {{ $percent }}%"></div>
                    </div>

                    <div class="bottom">
                        <span>{{ $days }} / 90 Hari</span>
                        <span>Panen: {{ $estimasiPanen }}</span>
                    </div>

                </div>

                <!-- ACTION -->
                <div class="budidaya-action">

                    <button class="edit-btn" onclick="openBudidayaModal()">
                        ✏️ Edit Tanggal
                    </button>

                    <button class="edit-btn danger" onclick="resetBudidaya()">
                        🔄 Reset
                    </button>

                </div>

            @endif

        </div>

    </div>

</div>

<!-- ================= MODAL PROFILE ================= -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>Edit Profil Tambak</h3>

        <form action="{{ url('/profile-tambak/update') }}" method="POST">
            @csrf
            @method('PUT')

            <input type="text" name="nama_tambak" value="{{ $profile->nama_tambak ?? '' }}">
            <input type="text" name="lokasi" value="{{ $profile->lokasi ?? '' }}">
            <input type="number" step="0.01" name="luas" value="{{ $profile->luas ?? '' }}">
            <input type="text" name="tipe_tambak" value="{{ $profile->tipe_tambak ?? '' }}">
            <input type="date" name="tanggal_dibuat" value="{{ $profile->tanggal_dibuat ?? '' }}">

            <div class="modal-action">
                <button type="button" onclick="closeEditModal()">Batal</button>
                <button type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL BIOMASSA ================= -->
<div id="biomassaModal" class="modal">
    <div class="modal-content">
        <h3>Edit Biomassa</h3>

        <form action="{{ url('/profile-tambak/biomassa/update') }}" method="POST">
            @csrf
            @method('PUT')

            <input type="number" name="biomassa_udang"
                   value="{{ $profile->biomassa_udang ?? 0 }}">

            <div class="modal-action">
                <button type="button" onclick="closeBiomassa()">Batal</button>
                <button type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL BUDIDAYA ================= -->
<div id="budidayaModal" class="modal">
    <div class="modal-content">

        <h3>Mulai / Edit Budidaya</h3>

        <form action="{{ url('/budidaya/start') }}" method="POST">
            @csrf
            @method('PUT')

            <label>Tanggal Mulai Budidaya</label>
            <input type="date"
                   name="tanggal_mulai_budidaya"
                   value="{{ $profile->tanggal_mulai_budidaya ?? '' }}"
                   required>

            <div class="modal-action">
                <button type="button" onclick="closeBudidayaModal()">Batal</button>
                <button type="submit">Simpan</button>
            </div>

        </form>

    </div>
</div>
{{-- ================= TOAST ================= --}}

@if(session('success'))
    <div class="toast success" id="toast">
        ✅ {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="toast error" id="toast">
        ❌ {{ session('error') }}
    </div>
@endif
<script src="{{ asset('js/profile.js') }}"></script>

@endsection