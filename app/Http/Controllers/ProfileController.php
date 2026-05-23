<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\TambakProfile;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        // 🔥 AMBIL DATA TERBARU LANGSUNG DARI DATABASE
        $profile = TambakProfile::first();
        return view('dashboard.profile', compact('profile'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'nama_tambak'    => 'nullable|string|max:255',
            'lokasi'         => 'nullable|string|max:255',
            'luas'           => 'nullable|numeric|min:0',
            'tipe_tambak'    => 'nullable|string|max:100',
            'tanggal_dibuat' => 'nullable|date',
            'populasi'       => 'nullable|integer|min:0',
            'avg_weight'     => 'nullable|numeric|min:0',
            'foto_tambak'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $profile = TambakProfile::first();
        
        if (!$profile) {
            $profile = new TambakProfile();
        }

        $profile->nama_tambak = $request->nama_tambak;
        $profile->lokasi = $request->lokasi;
        $profile->luas = $request->luas;
        $profile->tipe_tambak = $request->tipe_tambak;
        $profile->tanggal_dibuat = $request->tanggal_dibuat;
        $profile->populasi = $request->populasi ?? 5000;
        $profile->avg_weight = $request->avg_weight ?? 15;

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
        $request->validate([
            'tanggal_mulai_budidaya' => 'required|date'
        ]);

        $profile = TambakProfile::first();
        if (!$profile) {
            $profile = new TambakProfile();
        }

        $profile->tanggal_mulai_budidaya = $request->tanggal_mulai_budidaya;
        $profile->save();

        return redirect()->route('profile.index')->with('success', '✅ Budidaya berhasil dimulai');
    }

    public function resetBudidaya()
    {
        $profile = TambakProfile::first();
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
        $request->validate([
            'biomassa_udang' => 'required|numeric|min:0'
        ]);

        $profile = TambakProfile::first();
        if (!$profile) {
            $profile = new TambakProfile();
        }

        $profile->biomassa_udang = $request->biomassa_udang;
        $profile->save();

        return redirect()->route('profile.index')->with('success', '✅ Biomassa berhasil diperbarui');
    }
}