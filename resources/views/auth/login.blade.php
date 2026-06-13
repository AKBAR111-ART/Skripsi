<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login | Tambak Mandhala</title>
    
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
        
        .login-container {
            position: relative;
            z-index: 1;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="login-container max-w-5xl w-full mx-auto">
        <div class="grid md:grid-cols-2 gap-0 rounded-3xl overflow-hidden shadow-2xl">
            
            <!-- LEFT SIDE - BRANDING -->
            <div class="bg-gradient-to-br from-teal-800 to-teal-600 p-8 md:p-10 flex flex-col justify-between">
                <div>
                    <div class="flex items-center space-x-3 mb-8">
                        <img src="{{ asset('images/logo-udang.png') }}" class="h-12 w-12 object-contain" alt="Logo">
                        <span class="text-white text-2xl font-bold">Tambak Mandhala</span>
                    </div>
                    <h1 class="text-white text-3xl md:text-4xl font-bold mb-4">
                        Sistem Rekomendasi<br>
                        Pakan Udang
                    </h1>
                    <p class="text-teal-100 text-lg mb-8">
                        Optimalkan pemberian pakan udang Anda dengan rekomendasi cerdas berbasis data real-time.
                    </p>
                </div>
                
                <div class="space-y-3">
                    <div class="flex items-center space-x-3 text-teal-100">
                        <i class="fas fa-chart-line w-5"></i>
                        <span>Analisis kualitas air real-time</span>
                    </div>
                    <div class="flex items-center space-x-3 text-teal-100">
                        <i class="fas fa-calculator w-5"></i>
                        <span>Perhitungan dosis pakan otomatis</span>
                    </div>
                    <div class="flex items-center space-x-3 text-teal-100">
                        <i class="fas fa-calendar-alt w-5"></i>
                        <span>Jadwal pakan terjadwal</span>
                    </div>
                    <div class="flex items-center space-x-3 text-teal-100">
                        <i class="fas fa-chart-pie w-5"></i>
                        <span>Monitoring pertumbuhan udang</span>
                    </div>
                </div>
            </div>

            <!-- RIGHT SIDE - LOGIN FORM -->
            <div class="glass-card p-8 md:p-10">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-800">Selamat Datang Kembali</h2>
                    <p class="text-gray-500 mt-2">Masuk ke akun petambak Anda</p>
                </div>

                @if(session('success'))
                    <div class="mb-4 p-3 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm flex items-center">
                        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                        @foreach($errors->all() as $error)
                            <div class="flex items-center"><i class="fas fa-times-circle mr-2"></i> {{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ url('/login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-phone-alt mr-2 text-teal-600"></i> Nomor HP / Email
                        </label>
                        <input type="text" name="username" value="{{ old('username') }}" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                               placeholder="0812-3456-7890 atau email@contoh.com" required>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-lock mr-2 text-teal-600"></i> Password
                        </label>
                        <div class="relative">
                            <input type="password" name="password" id="password"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                   placeholder="Masukkan password" required>
                            <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-teal-600">
                                <i id="eyeIcon" class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-teal-600 rounded border-gray-300 focus:ring-teal-500">
                            <span class="text-sm text-gray-600">Ingat saya</span>
                        </label>
                        <a href="{{ url('/forgot-password') }}" class="text-sm text-teal-600 hover:text-teal-700 font-medium">
                            Lupa password?
                        </a>
                    </div>

                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-teal-600 to-teal-500 text-white py-3 rounded-xl font-semibold hover:from-teal-700 hover:to-teal-600 transition duration-200 shadow-lg">
                        <i class="fas fa-sign-in-alt mr-2"></i> Masuk ke Dashboard
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                    <p class="text-xs text-gray-500">
                        <i class="fas fa-shield-alt mr-1"></i> Sistem aman untuk data tambak Anda
                    </p>
                    <p class="text-xs text-gray-400 mt-2">
                        Butuh bantuan? Hubungi <span class="text-teal-600 font-medium">0811-2233-4455</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (password.type === 'password') {
                password.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>