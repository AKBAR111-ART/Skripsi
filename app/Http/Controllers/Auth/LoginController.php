<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (session()->has('user_id')) {
            return redirect('/dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('phone', $request->username)
                    ->orWhere('email', $request->username)
                    ->first();

        if (!$user) {
            return back()->with('error', 'Username tidak ditemukan');
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Password salah');
        }

        session([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_phone' => $user->phone,
            'user_email' => $user->email,
            'tambak_name' => $user->tambak_name,
            'lokasi_tambak' => $user->lokasi_tambak,
        ]);

        return redirect('/dashboard')->with('success', 'Selamat datang, ' . $user->name);
    }

    public function logout()
    {
        session()->flush();
        return redirect('/')->with('success', 'Anda telah logout');
    }
}