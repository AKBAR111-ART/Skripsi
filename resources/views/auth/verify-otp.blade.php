<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verifikasi OTP | Tambak Mandhala</title>
    
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
        .verify-container { position: relative; z-index: 1; }
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="verify-container max-w-md w-full mx-auto">
        <div class="glass-card rounded-3xl overflow-hidden shadow-2xl">
            
            <div class="bg-gradient-to-br from-teal-800 to-teal-600 p-6 text-center">
                <div class="flex items-center justify-center space-x-3 mb-4">
                    <img src="{{ asset('images/logo-udang.png') }}" class="h-12 w-12 object-contain" alt="Logo">
                    <span class="text-white text-2xl font-bold">Tambak Mandhala</span>
                </div>
                <h1 class="text-white text-xl font-bold">Verifikasi Kode OTP</h1>
                <p class="text-teal-100 text-sm mt-1">Masukkan kode yang telah dikirim</p>
            </div>

            <div class="p-8">
                @if(session('success'))
                    <div class="mb-4 p-3 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm flex items-center">
                        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                    </div>
                @endif

                @if(session('wa_sent'))
                    <div class="mb-4 p-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 text-sm flex items-center">
                        <i class="fab fa-whatsapp mr-2 text-green-500"></i> Kode OTP juga telah dikirim via WhatsApp!
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                        @foreach($errors->all() as $error)
                            <div class="flex items-center"><i class="fas fa-times-circle mr-2"></i> {{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ url('/verify-otp') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-gray-700 font-medium mb-2 text-center">
                            <i class="fas fa-key mr-2 text-teal-600"></i> Kode OTP (6 digit)
                        </label>
                        <input type="text" name="otp_code" maxlength="6" required autofocus
                               class="w-full px-4 py-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 text-center text-2xl tracking-widest font-mono"
                               placeholder="000000">
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-teal-600 to-teal-500 text-white py-3 rounded-xl font-semibold hover:from-teal-700 hover:to-teal-600 transition shadow-lg">
                        <i class="fas fa-check-circle mr-2"></i> Verifikasi OTP
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="{{ url('/forgot-password') }}" class="text-teal-600 font-medium hover:underline">
                        <i class="fas fa-redo-alt mr-1"></i> Kirim Ulang Kode
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>