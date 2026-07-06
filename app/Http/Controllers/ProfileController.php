<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\TambakProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
public function index()
{
    
    $user = Auth::user();
    $profile = TambakProfile::where('user_id', $user->id)->first();
    
    if (!$profile) {
        $profile = new TambakProfile();
        $profile->user_id = $user->id;
        $profile->save();
    }
    
    return view('dashboard.profile', compact('profile', 'user'));
}
    public function update(Request $request)
    {
        // 🔥 CEK APAKAH USER LOGIN
        $user = Auth::user();
        
        if (!$user) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }
        
        $request->validate([
            'nama_tambak'    => 'nullable|string|max:255',
            'lokasi'         => 'nullable|string|max:255',
            'luas'           => 'nullable|numeric|min:0',
            'tipe_tambak'    => 'nullable|string|max:100',
            'tanggal_dibuat' => 'nullable|date',
            'populasi'       => 'nullable|integer|min:0',
            'avg_weight'     => 'nullable|numeric|min:0',
            'foto_tambak'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'name'           => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:20',
        ]);

        // Update user yang login
        $userData = [];
        if ($request->filled('name')) {
            $userData['name'] = $request->name;
        }
        if ($request->filled('email') && $request->email !== $user->email) {
            $userData['email'] = $request->email;
        }
        if ($request->filled('phone')) {
            $userData['phone'] = $request->phone;
        }
        if ($request->filled('nama_tambak')) {
            $userData['tambak_name'] = $request->nama_tambak;
        }
        if ($request->filled('lokasi')) {
            $userData['lokasi_tambak'] = $request->lokasi;
        }
        if ($request->filled('populasi')) {
            $userData['populasi'] = $request->populasi;
        }
        
        if (!empty($userData)) {
            User::where('id', $user->id)->update($userData);
            
            // Refresh user data
            $user = Auth::user();
            
            // Update session
            session([
                'user_name' => $user->name,
                'user_phone' => $user->phone,
                'user_email' => $user->email,
                'tambak_name' => $user->tambak_name,
                'lokasi_tambak' => $user->lokasi_tambak,
            ]);
        }

        // Update atau buat tambak_profile
        $profile = TambakProfile::where('user_id', $user->id)->first();
        
        if (!$profile) {
            $profile = new TambakProfile();
            $profile->user_id = $user->id;
        }

        $profile->nama_tambak = $request->nama_tambak ?? $profile->nama_tambak;
        $profile->lokasi = $request->lokasi ?? $profile->lokasi;
        $profile->luas = $request->luas ?? $profile->luas;
        $profile->tipe_tambak = $request->tipe_tambak ?? $profile->tipe_tambak;
        $profile->tanggal_dibuat = $request->tanggal_dibuat ?? $profile->tanggal_dibuat;
        $profile->populasi = $request->populasi ?? $profile->populasi ?? 5000;
        $profile->avg_weight = $request->avg_weight ?? $profile->avg_weight ?? 15;

        if ($request->hasFile('foto_tambak')) {
            if ($profile->foto_tambak && Storage::disk('public')->exists($profile->foto_tambak)) {
                Storage::disk('public')->delete($profile->foto_tambak);
            }
            $file = $request->file('foto_tambak');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('tambak', $filename, 'public');
            $profile->foto_tambak = $path;
        }

        $profile->save();

        // 🔥 REDIRECT DENGAN SESSION SUCCESS
        return redirect()->route('profile.index')->with('success', '✅ Profil berhasil diperbarui');
    }

    public function startBudidaya(Request $request)
    {
        // 🔥 CEK APAKAH USER LOGIN
        $user = Auth::user();
        
        if (!$user) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }
        
        $request->validate([
            'tanggal_mulai_budidaya' => 'required|date'
        ]);

        $profile = TambakProfile::where('user_id', $user->id)->first();
        
        if (!$profile) {
            $profile = new TambakProfile();
            $profile->user_id = $user->id;
        }

        $profile->tanggal_mulai_budidaya = $request->tanggal_mulai_budidaya;
        $profile->save();

        return redirect()->route('profile.index')->with('success', '✅ Budidaya berhasil dimulai');
    }

    public function resetBudidaya()
    {
        // 🔥 CEK APAKAH USER LOGIN
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => true, 'message' => 'Silakan login terlebih dahulu'], 401);
        }
        
        $profile = TambakProfile::where('user_id', $user->id)->first();
        
        if (!$profile) {
            return response()->json(['error' => true, 'message' => 'Profile tidak ditemukan'], 404);
        }

        $profile->tanggal_mulai_budidaya = Carbon::now();
        $profile->save();

        return response()->json([
            'success' => true,
            'message' => '🔄 Budidaya berhasil direset ke hari ini'
        ]);
    }

    public function updateBiomassa(Request $request)
    {
        // 🔥 CEK APAKAH USER LOGIN
        $user = Auth::user();
        
        if (!$user) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }
        
        $request->validate([
            'biomassa_udang' => 'required|numeric|min:0'
        ]);

        $profile = TambakProfile::where('user_id', $user->id)->first();
        
        if (!$profile) {
            $profile = new TambakProfile();
            $profile->user_id = $user->id;
        }

        $profile->biomassa_udang = $request->biomassa_udang;
        $profile->save();

        return redirect()->route('profile.index')->with('success', '✅ Biomassa berhasil diperbarui');
    }

    public function getRealtimeData()
    {
        // 🔥 CEK APAKAH USER LOGIN
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => true, 'message' => 'Unauthorized'], 401);
        }
        
        $profile = TambakProfile::where('user_id', $user->id)->first();
        
        return response()->json([
            'success' => true,
            'data' => $profile
        ]);
    }
    
    public function getLatestProfile()
    {
        // 🔥 CEK APAKAH USER LOGIN
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => true, 'message' => 'Unauthorized'], 401);
        }
        
        $profile = TambakProfile::where('user_id', $user->id)->first();
        
        return response()->json([
            'success' => true,
            'data' => $profile
        ]);
    }
}