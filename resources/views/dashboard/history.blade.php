@extends('footbar.utama')

@section('title', 'Halaman History')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/history.css') }}">
<script src="https://unpkg.com/feather-icons"></script>
@endpush

@section('content')

<div class="section-header">

    <div class="section-left">
        <div class="icon-box">
    <i data-feather="clock"></i>
</div>

        <div class="text-box">
            <h2>History Monitoring</h2>
            <p>History yang menampilkan data monitoring selama budidaya dari bibit sampai panen</p>
        </div>
    </div>

    <div class="section-right">
        <button class="btn-help">❓ Bantuan</button>
    </div>

</div>

    <div class="slider-wrapper">

        {{-- BUTTON LEFT --}}
        <button class="nav-btn left" id="prevBtn" type="button">‹</button>

        <div class="week-slider">

            @php
                $days = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
            @endphp

            {{-- LOOP MINGGU --}}
            @for ($i = 1; $i <= 12; $i++)
            <div class="card-week" data-week="{{ $i }}">

                {{-- HEADER --}}
                <div class="week-header">
                    <h3>Minggu {{ $i }}</h3>
                    <span class="arrow">▼</span>
                </div>

                {{-- SUMMARY --}}
                <div class="week-summary">
                    <p>pH </p>
                    <p>Turbidity</p>
                    <p>Pakan <b>total : 3.2kg</b></p>
                </div>

                {{-- DAYS --}}
                <div class="week-days">
                    @foreach ($days as $day)
                    <div class="card-day"
                         data-day="{{ $day }}"
                         data-week="{{ $i }}">
                        <div class="day-info">
                            <span>{{ $day }}</span>
                            <span>pH 7.5</span>
                        </div>
                    </div>
                    @endforeach
                </div>

            </div>
            @endfor

        </div>

        {{-- BUTTON RIGHT --}}
        <button class="nav-btn right" id="nextBtn" type="button">›</button>

    </div>

</div>

{{-- DETAIL SHEET --}}
<div id="detailSheet" class="sheet">
    <div class="sheet-content">

        <button id="closeSheet" type="button">✕</button>

        <h3 id="dayTitle"></h3>

        {{-- TABLE DATA --}}
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>pH</th>
                        <th>Turbidity</th>
                    </tr>
                </thead>
                <tbody id="dataTable"></tbody>
            </table>
        </div>

        {{-- FEED --}}
        <h4>Pemberian Pakan</h4>
        <div id="feedList"></div>

    </div>
</div>
<div class="info-cards">

    <div class="info-card">
        <div class="info-left">
            <i data-feather="activity"></i>
            <div>
                <span class="title">pH Air</span>
                <small>Menunjukkan tingkat keasaman air tambak</small>
            </div>
        </div>
        <div class="info-right">7.5</div>
    </div>

    <div class="info-card">
        <div class="info-left">
            <i data-feather="droplet"></i>
            <div>
                <span class="title">Turbidity</span>
                <small>Tingkat kekeruhan air</small>
            </div>
        </div>
        <div class="info-right">12 NTU</div>
    </div>

    <div class="info-card">
        <div class="info-left">
            <i data-feather="package"></i>
            <div>
                <span class="title">Pakan Harian</span>
                <small>Total pemberian pakan hari ini</small>
            </div>
        </div>
        <div class="info-right">3.2 kg</div>
    </div>

</div>

@endsection

@push('scripts')
<script src="{{ asset('js/history.js') }}"></script>
@endpush