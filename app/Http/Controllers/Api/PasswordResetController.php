<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Kirim OTP ke nomor HP (via email atau SMS)
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|exists:users,no_hp'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Nomor HP tidak ditemukan',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cari user berdasarkan no_hp
        $user = User::where('no_hp', $request->phone)->first();
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Nomor HP tidak terdaftar'
            ], 404);
        }

        // Hapus OTP lama untuk user ini
        PasswordReset::where('email', $user->email)->delete();

        // Generate OTP 6 digit
        $otp = rand(100000, 999999);
        $expiresAt = now()->addMinutes(10); // berlaku 10 menit

        // Simpan OTP ke database
        PasswordReset::create([
            'email' => $user->email,
            'otp_code' => $otp,
            'expires_at' => $expiresAt,
            'is_used' => false
        ]);

        // Kirim OTP via email (karena user punya email)
        try {
            Mail::send([], [], function ($message) use ($user, $otp) {
                $message->to($user->email)
                        ->subject('Kode OTP Reset Password - Tambak Mandhala')
                        ->setBody("Halo {$user->name},\n\nAnda meminta reset password.\n\nKode OTP Anda: {$otp}\n\nKode ini berlaku selama 10 menit.\n\nJika Anda tidak meminta reset password, abaikan email ini.\n\n- Tim Tambak Mandhala");
            });

            return response()->json([
                'status' => true,
                'message' => 'Kode OTP telah dikirim ke email Anda',
                'otp' => $otp // HAPUS baris ini di production, hanya untuk testing
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengirim email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifikasi OTP
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp_code' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cari user berdasarkan no_hp
        $user = User::where('no_hp', $request->phone)->first();
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Nomor HP tidak terdaftar'
            ], 404);
        }

        // Cari OTP yang valid
        $reset = PasswordReset::where('email', $user->email)
                    ->where('otp_code', $request->otp_code)
                    ->where('is_used', false)
                    ->where('expires_at', '>', now())
                    ->first();

        if (!$reset) {
            return response()->json([
                'status' => false,
                'message' => 'Kode OTP tidak valid atau sudah kadaluarsa'
            ], 400);
        }

        // Tandai OTP sebagai sudah digunakan
        $reset->update(['is_used' => true]);

        // Generate token sementara untuk reset password
        $tempToken = Str::random(60);
        $reset->update(['temp_token' => $tempToken]);

        return response()->json([
            'status' => true,
            'message' => 'OTP valid',
            'data' => [
                'temp_token' => $tempToken,
                'email' => $user->email,
                'phone' => $user->no_hp
            ]
        ]);
    }

    /**
     * Reset password dengan token
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'temp_token' => 'required|string',
            'password' => 'required|string|min:6|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cari user berdasarkan no_hp
        $user = User::where('no_hp', $request->phone)->first();
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Nomor HP tidak terdaftar'
            ], 404);
        }

        // Cari token yang valid
        $reset = PasswordReset::where('email', $user->email)
                    ->where('temp_token', $request->temp_token)
                    ->where('is_used', true)
                    ->first();

        if (!$reset) {
            return response()->json([
                'status' => false,
                'message' => 'Token tidak valid'
            ], 400);
        }

        // Update password user
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Hapus semua OTP untuk email ini
        PasswordReset::where('email', $user->email)->delete();

        // Hapus semua token user (logout dari semua device)
        $user->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Password berhasil direset, silakan login dengan password baru'
        ]);
    }
}