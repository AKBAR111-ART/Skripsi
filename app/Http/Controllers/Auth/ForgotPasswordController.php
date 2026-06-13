<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|exists:users,phone',
        ]);

        $user = User::where('phone', $request->phone)->first();
        
        // Generate token sederhana (untuk demo)
        $token = Str::random(60);
        
        // Simpan token (bisa di cache atau table)
        session(['reset_token_' . $user->id => $token]);
        
        // Redirect ke reset password (tanpa email untuk kemudahan demo)
        return redirect('/reset-password/' . $user->id . '?token=' . $token);
    }
}