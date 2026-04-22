@extends('footbar.utama')

@section('title', 'Halaman Pengaturan')

@section('content')

<link rel="stylesheet" href="{{ asset('css/pengaturan.css') }}">

<div class="container">

    <!-- HEADER -->
    <div class="header">
        <div class="header-left">
            <div class="icon">⚙️</div>
            <div>
                <h1>Pengaturan Rekomendasi Pakan Tambak Udang</h1>
                <small>Atur variabel input, rule (aturan), dan parameter output untuk sistem rekomendasi pakan.</small>
            </div>
        </div>

        <div class="header-right">
            <button class="btn-help">❓ Bantuan</button>
        </div>
    </div>

    <!-- GRID -->
    <div class="grid">

        <!-- VARIABEL -->
        <div class="card">
            <h2>Variabel Input</h2>
            <p>Kelola variabel yang digunakan dalam perhitungan rekomendasi pakan.</p>

            <table>
                <thead>
                    <tr>
                        <th>Variabel</th>
                        <th>Satuan</th>
                        <th>Rentang</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>💧 pH <br><small>Derajat keasaman air</small></td>
                        <td>-</td>
                        <td>7.5 - 8.5</td>
                        <td><button class="btn-edit">✏️</button></td>
                    </tr>
                    <tr>
                        <td>⚪ Turbidity <br><small>Kekeruhan air</small></td>
                        <td>NTU</td>
                        <td>10 - 50</td>
                        <td><button class="btn-edit">✏️</button></td>
                    </tr>
                </tbody>
            </table>

            <button class="btn-add">+ Tambah Variabel</button>

            <div class="info-box">
                Rentang normal digunakan sebagai referensi kondisi optimal.
                Nilai di luar rentang dapat mempengaruhi rekomendasi pakan.
            </div>
        </div>

        <!-- RULE -->
        <div class="card">
            <h2>Metode Rule-Based</h2>

            <div class="rule-box">
                <h3>📋 Cara Kerja</h3>
                <ol>
                    <li>Sistem membaca nilai pH dan Turbidity dari input.</li>
                    <li>Nilai dibandingkan dengan aturan (rule).</li>
                    <li>Rule menghasilkan rekomendasi pakan.</li>
                    <li>Prioritas tertinggi dipilih.</li>
                </ol>
            </div>

            <div class="rule-footer">
                <div>
                    <p>Jumlah Aturan Aktif</p>
                    <span class="badge">6 Rule</span>
                </div>

                <div>
                    <p>Status Sistem</p>
                    <span class="active-dot">● Aktif</span>
                </div>

                <button class="btn-rule">Kelola Rule / Aturan</button>
            </div>
        </div>

    </div>

    <!-- OUTPUT -->
    <div class="card output">

        <div class="output-left">
            <h2>Output Rekomendasi</h2>
            <p>Atur bagaimana hasil rekomendasi pakan ditampilkan dan dihitung.</p>

            <label>Satuan Output Pakan</label>
            <select>
                <option>% Biomassa / hari</option>
            </select>

            <label>Frekuensi Rekomendasi</label>
            <select>
                <option>Setiap 6 Jam</option>
            </select>

            <label>Tipe Pakan</label>
            <select>
                <option>All (Semua Jenis)</option>
            </select>
        </div>

        <div class="output-right">
            <h3>Contoh Output</h3>
            <h1 class="highlight">2.6 % Biomassa / hari</h1>
            <p>Rekomendasi berdasarkan kondisi pH dan Turbidity.</p>

            <div class="shrimp"></div>
        </div>

    </div>

    <!-- ACTION -->
    <div class="actions">
        <button class="btn-reset">Reset ke Default</button>
        <button class="btn-save">Simpan Pengaturan</button>
    </div>

</div>

<script src="{{ asset('js/pengaturan.js') }}"></script>

@endsection