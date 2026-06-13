<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password | Sistem Pakan Udang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gradient-to-br from-teal-700 to-teal-500 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8">
        <div class="text-center mb-6">
            <i class="fas fa-shrimp text-4xl text-teal-600 mb-3"></i>
            <h1 class="text-2xl font-bold text-gray-800">Lupa Password?</h1>
            <p class="text-gray-500 text-sm mt-2">Masukkan Nomor HP Anda untuk reset password</p>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ url('/forgot-password') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Nomor HP Terdaftar</label>
                <input type="tel" name="phone" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent" placeholder="0812-3456-7890" required>
            </div>
            <button type="submit" class="w-full bg-teal-600 text-white py-3 rounded-xl font-semibold hover:bg-teal-700 transition">
                Kirim Link Reset Password
            </button>
        </form>

        <div class="text-center mt-6">
            <a href="{{ url('/login') }}" class="text-teal-600 hover:text-teal-700 text-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Login
            </a>
        </div>
    </div>
</body>
</html>