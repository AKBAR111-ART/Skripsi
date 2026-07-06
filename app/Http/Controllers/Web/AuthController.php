<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TambakProfile;
use App\Models\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // Proses login
    public function doLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->username;
        $field = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        
        $user = User::where($field, $username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Username/Nomor HP atau password salah!');
        }

        Auth::login($user);
        
        session([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_phone' => $user->phone,
            'user_email' => $user->email,
        ]);

        return redirect('/dashboard')->with('success', 'Selamat datang kembali!');
    }

    // Proses registrasi dengan sinkronisasi ke tambak_profile
    public function doRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:6|confirmed',
            'tambak_name' => 'required|string|max:255',
            'lokasi_tambak' => 'required|string|max:255',
            'populasi' => 'nullable|integer|min:0',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        
        // Buat user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $phone,
            'password' => Hash::make($request->password),
            'tambak_name' => $request->tambak_name,
            'lokasi_tambak' => $request->lokasi_tambak,
            'populasi' => $request->populasi ?? 0,
            'role' => 'petambak',
            'is_verified' => true,
        ]);

        // 🔥 BUAT TAMBAK_PROFILE YANG TERHUBUNG DENGAN USER
        try {
            TambakProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'user_id' => $user->id,
                    'nama_tambak' => $request->tambak_name,
                    'lokasi' => $request->lokasi_tambak,
                    'populasi' => $request->populasi ?? 0,
                    'tanggal_mulai_budidaya' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            // Jika kolom tertentu tidak ada, insert minimal
            \Illuminate\Support\Facades\DB::table('tambak_profile')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Login user
        Auth::login($user);
        
        session([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_phone' => $user->phone,
            'user_email' => $user->email,
            'tambak_name' => $user->tambak_name,
            'lokasi_tambak' => $user->lokasi_tambak,
        ]);

        return redirect('/dashboard')->with('success', 'Registrasi berhasil! Selamat datang di Tambak Mandhala.');
    }

    // Proses lupa password - kirim OTP
    public function doForgotPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|string'
        ]);

        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return back()->with('error', 'Nomor HP tidak terdaftar!');
        }

        // Hapus OTP lama
        PasswordReset::where('email', $user->email)->delete();

        // Generate OTP
        $otp = rand(100000, 999999);
        $expiresAt = now()->addMinutes(10);

        PasswordReset::create([
            'email' => $user->email,
            'otp_code' => $otp,
            'expires_at' => $expiresAt,
            'is_used' => false
        ]);

        // Kirim ke Email
        try {
            Mail::raw("Kode OTP Anda: $otp\n\nBerlaku 10 menit.", function ($message) use ($user) {
                $message->to($user->email)->subject('Kode OTP Reset Password');
            });
        } catch (\Exception $e) {}

        // Kirim ke WhatsApp (hanya untuk nomor tertentu)
        $allowedWaNumbers = ['62895379348181', '6281234567890'];
        $userPhone = $phone;
        if (substr($userPhone, 0, 1) === '0') $userPhone = '62' . substr($userPhone, 1);
        
        if (in_array($userPhone, $allowedWaNumbers)) {
            try {
                Http::withHeaders(['Authorization' => env('FONNTE_TOKEN')])->post('https://api.fonnte.com/send', [
                    'target' => $userPhone,
                    'message' => "🔐 *TAMBAK MANDHALA*\n\nKode OTP Anda: $otp\nBerlaku 10 menit."
                ]);
                session(['wa_sent' => true]);
            } catch (\Exception $e) {}
        }

        session(['reset_phone' => $phone, 'reset_email' => $user->email]);

        return redirect('/verify-otp')->with('success', 'Kode OTP telah dikirim ke email Anda');
    }

    // Halaman verifikasi OTP
    public function showVerifyOtp()
    {
        if (!session('reset_phone')) {
            return redirect('/forgot-password')->with('error', 'Silakan masukkan nomor HP terlebih dahulu.');
        }
        return view('auth.verify-otp');
    }

    // Proses verifikasi OTP
    public function doVerifyOtp(Request $request)
    {
        $request->validate(['otp_code' => 'required|string|size:6']);

        $phone = session('reset_phone');
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return redirect('/forgot-password')->with('error', 'Session expired.');
        }

        $reset = PasswordReset::where('email', $user->email)
                    ->where('otp_code', $request->otp_code)
                    ->where('is_used', false)
                    ->where('expires_at', '>', now())
                    ->first();

        if (!$reset) {
            return back()->with('error', 'Kode OTP tidak valid atau sudah kadaluarsa.');
        }

        $reset->update(['is_used' => true]);

        $tempToken = Str::random(60);
        $reset->update(['temp_token' => $tempToken]);

        session(['reset_token' => $tempToken, 'reset_email' => $user->email]);

        return redirect('/reset-password')->with('success', 'OTP valid. Silakan buat password baru.');
    }

    // Halaman reset password
    public function showResetPassword()
    {
        if (!session('reset_token')) {
            return redirect('/forgot-password');
        }
        return view('auth.reset-password');
    }

    // Proses reset password
    public function doResetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed'
        ]);

        $email = session('reset_email');
        $tempToken = session('reset_token');

        $reset = PasswordReset::where('email', $email)
                    ->where('temp_token', $tempToken)
                    ->where('is_used', true)
                    ->first();

        if (!$reset) {
            return redirect('/forgot-password')->with('error', 'Session tidak valid.');
        }

        $user = User::where('email', $email)->first();
        $user->update(['password' => Hash::make($request->password)]);

        PasswordReset::where('email', $email)->delete();
        session()->forget(['reset_phone', 'reset_email', 'reset_token']);

        return redirect('/login')->with('success', 'Password berhasil direset!');
    }

    // Logout
    public function doLogout()
    {
        Auth::logout();
        session()->flush();
        return redirect('/login')->with('success', 'Anda telah logout.');
    }
}