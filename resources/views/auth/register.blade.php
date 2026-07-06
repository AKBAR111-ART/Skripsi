<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registrasi | Tambak Mandhala</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            background: url('{{ asset('images/udang.png') }}') no-repeat center center fixed;
            background-size: cover;
            position: relative;
        }
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 0;
        }
        .register-container { position: relative; z-index: 1; }
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="register-container max-w-5xl w-full mx-auto">
        <div class="grid md:grid-cols-2 gap-0 rounded-3xl overflow-hidden shadow-2xl">
            
            <!-- LEFT SIDE -->
            <div class="bg-gradient-to-br from-teal-800 to-teal-600 p-8 md:p-10 flex flex-col justify-between">
                <div>
                    <div class="flex items-center space-x-3 mb-8">
                        <img src="{{ asset('images/logo-udang.png') }}" class="h-12 w-12 object-contain" alt="Logo">
                        <span class="text-white text-2xl font-bold">Tambak Mandhala</span>
                    </div>
                    <h1 class="text-white text-3xl md:text-4xl font-bold mb-4">Daftar Akun Baru</h1>
                    <p class="text-teal-100 text-lg mb-8">Bergabunglah untuk mengelola tambak udang Anda.</p>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center space-x-3 text-teal-100"><i class="fas fa-chart-line w-5"></i><span>Analisis kualitas air real-time</span></div>
                    <div class="flex items-center space-x-3 text-teal-100"><i class="fas fa-calculator w-5"></i><span>Perhitungan dosis pakan otomatis</span></div>
                    <div class="flex items-center space-x-3 text-teal-100"><i class="fas fa-calendar-alt w-5"></i><span>Jadwal pakan terjadwal</span></div>
                    <div class="flex items-center space-x-3 text-teal-100"><i class="fas fa-chart-pie w-5"></i><span>Monitoring pertumbuhan udang</span></div>
                </div>
            </div>

            <!-- RIGHT SIDE - REGISTER FORM -->
            <div class="glass-card p-8 md:p-10 overflow-y-auto max-h-screen">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Daftar Akun Baru</h2>
                    <p class="text-gray-500 mt-2">Isi data berikut untuk memulai</p>
                </div>

                @if($errors->any())
                    <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                        @foreach($errors->all() as $error)
                            <div class="flex items-center"><i class="fas fa-times-circle mr-2"></i> {{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ url('/register') }}" class="space-y-4">
                    @csrf

                    <div class="border-b border-gray-200 pb-2 mb-2">
                        <h3 class="text-md font-semibold text-teal-600"><i class="fas fa-user-circle mr-2"></i> Data Akun</h3>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="Masukkan nama lengkap">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="email@contoh.com">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Nomor HP <span class="text-red-500">*</span></label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="081234567890">
                        <p class="text-gray-400 text-xs mt-1">Digunakan untuk reset password</p>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" name="password" id="password" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="Minimal 6 karakter">
                            <button type="button" onclick="togglePassword('password')" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Konfirmasi Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="password_confirmation" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="Ulangi password">
                            <button type="button" onclick="togglePassword('password_confirmation')" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>

                    <div class="border-b border-gray-200 pb-2 mb-2 mt-4">
                        <h3 class="text-md font-semibold text-teal-600"><i class="fas fa-fish mr-2"></i> Data Tambak</h3>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Nama Tambak <span class="text-red-500">*</span></label>
                        <input type="text" name="tambak_name" value="{{ old('tambak_name') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="Contoh: Tambak Berkah">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Lokasi Tambak <span class="text-red-500">*</span></label>
                        <input type="text" name="lokasi_tambak" value="{{ old('lokasi_tambak') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="Contoh: Desa Mangunharjo, Semarang">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Populasi Udang (ekor)</label>
                        <input type="number" name="populasi" value="{{ old('populasi') }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="Contoh: 10000">
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-teal-600 to-teal-500 text-white py-3 rounded-xl font-semibold hover:from-teal-700 hover:to-teal-600 transition shadow-lg">
                        <i class="fas fa-user-plus mr-2"></i> Daftar Sekarang
                    </button>
                </form>

                <div class="mt-6 pt-4 border-t border-gray-200 text-center">
                    <p class="text-gray-600 text-sm">Sudah punya akun? <a href="{{ url('/login') }}" class="text-teal-600 font-semibold hover:underline">Masuk di sini</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.parentElement.querySelector('i');
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>