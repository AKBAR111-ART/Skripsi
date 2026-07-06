<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lupa Password | Tambak Mandhala</title>
    
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
        .forgot-container { position: relative; z-index: 1; }
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="forgot-container max-w-md w-full mx-auto">
        <div class="glass-card rounded-3xl overflow-hidden shadow-2xl">
            
            <div class="bg-gradient-to-br from-teal-800 to-teal-600 p-6 text-center">
                <div class="flex items-center justify-center space-x-3 mb-4">
                    <img src="{{ asset('images/logo-udang.png') }}" class="h-12 w-12 object-contain" alt="Logo">
                    <span class="text-white text-2xl font-bold">Tambak Mandhala</span>
                </div>
                <h1 class="text-white text-xl font-bold">Lupa Password?</h1>
                <p class="text-teal-100 text-sm mt-1">Masukkan Nomor HP Anda untuk reset password</p>
            </div>

            <div class="p-8">
                @if(session('error'))
                    <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ url('/forgot-password') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-phone-alt mr-2 text-teal-600"></i> Nomor HP Terdaftar
                        </label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500"
                               placeholder="081234567890">
                        <p class="text-gray-400 text-xs mt-1">Masukkan nomor HP yang terdaftar (contoh: 081234567890)</p>
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-teal-600 to-teal-500 text-white py-3 rounded-xl font-semibold hover:from-teal-700 hover:to-teal-600 transition shadow-lg">
                        <i class="fas fa-paper-plane mr-2"></i> Kirim Kode OTP
                    </button>
                </form>

                <div class="mt-6 pt-4 border-t border-gray-200 text-center">
                    <a href="{{ url('/login') }}" class="text-teal-600 hover:text-teal-700 text-sm font-medium">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>