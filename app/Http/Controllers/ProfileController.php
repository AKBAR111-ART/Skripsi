<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\TambakProfile;

class ProfileController extends Controller
{
    // ======================
    // HALAMAN PROFILE
    // ======================
    public function index5()
    {
        $profile = TambakProfile::first();
        return view('dashboard.profile', compact('profile'));
    }

    // ======================
    // UPSERT PROFILE (FIXED)
    // ======================
    public function update(Request $request)
    {
        $request->validate([
            'nama_tambak' => 'nullable|string',
            'lokasi' => 'nullable|string',
            'luas' => 'nullable|numeric',
            'tipe_tambak' => 'nullable|string',
            'tanggal_dibuat' => 'nullable|date',
        ]);

        TambakProfile::updateOrCreate(
            ['id' => 1], 
            [
                'nama_tambak' => $request->nama_tambak,
                'lokasi' => $request->lokasi,
                'luas' => $request->luas,
                'tipe_tambak' => $request->tipe_tambak,
                'tanggal_dibuat' => $request->tanggal_dibuat,
            ]
        );

        return back()->with('success', 'Profil berhasil disimpan');
    }

    // ======================
    // START BUDIDAYA
    // ======================
    public function startBudidaya(Request $request)
    {
        $request->validate([
            'tanggal_mulai_budidaya' => 'required|date'
        ]);

        $profile = TambakProfile::first();

        if (!$profile) {
            return back()->with('error', 'Profile tidak ditemukan');
        }

        $profile->tanggal_mulai_budidaya = $request->tanggal_mulai_budidaya;
        $profile->save();

        return back()->with('success', 'Budidaya berhasil dimulai');
    }

    // ======================
    // RESET BUDIDAYA (FIXED)
    // ======================
   public function resetBudidaya()
{
    $profile = TambakProfile::first();

    if (!$profile) {
        return response()->json(['error' => true], 404);
    }

    $profile->tanggal_mulai_budidaya = Carbon::now(); // 🔥 reset ke hari ini
    $profile->save();

    return response()->json([
        'success' => true,
        'message' => 'Budidaya berhasil direset ke hari ini'
    ]);
    
}
    // ======================
    // UPDATE BIOMASSA
    // ======================
    public function updateBiomassa(Request $request)
    {
        $request->validate([
            'biomassa_udang' => 'required|numeric'
        ]);

        TambakProfile::updateOrCreate(

            // cari data id 1
            ['id' => 1],

            // update / insert
            [
                'biomassa_udang' => $request->biomassa_udang
            ]
        );

        return back()->with('success', 'Biomassa berhasil disimpan');
    }
}