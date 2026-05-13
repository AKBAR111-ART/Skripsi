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
    // UPDATE PROFILE + FOTO
    // ======================
    public function update(Request $request)
    {
        $request->validate([
            'nama_tambak'    => 'nullable|string',
            'lokasi'         => 'nullable|string',
            'luas'           => 'nullable|numeric',
            'tipe_tambak'    => 'nullable|string',
            'tanggal_dibuat' => 'nullable|date',

            // FOTO
            'foto_tambak'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // ======================
        // AMBIL DATA PERTAMA
        // ======================

        $profile = TambakProfile::first();

        // kalau belum ada data
        if (!$profile) {

            $profile = new TambakProfile();

        }

        // ======================
        // UPDATE DATA
        // ======================

        $profile->nama_tambak = $request->nama_tambak;
        $profile->lokasi = $request->lokasi;
        $profile->luas = $request->luas;
        $profile->tipe_tambak = $request->tipe_tambak;
        $profile->tanggal_dibuat = $request->tanggal_dibuat;

        // ======================
        // UPLOAD FOTO
        // ======================

        if ($request->hasFile('foto_tambak')) {

            $file = $request->file('foto_tambak');

            // nama unik
            $filename = time() . '.' . $file->getClientOriginalExtension();

            // simpan file
            $file->storeAs('tambak', $filename, 'public');

            // hapus foto lama
            if ($profile->foto_tambak) {

                $oldPath = storage_path(
                    'app/public/' . $profile->foto_tambak
                );

                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            // simpan path baru
            $profile->foto_tambak = 'tambak/' . $filename;
        }

        // ======================
        // SAVE
        // ======================

        $profile->save();

        return back()->with(
            'success',
            'Profil berhasil diperbarui'
        );
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

            return back()->with(
                'error',
                'Profile tidak ditemukan'
            );
        }

        $profile->tanggal_mulai_budidaya =
            $request->tanggal_mulai_budidaya;

        $profile->save();

        return back()->with(
            'success',
            'Budidaya berhasil dimulai'
        );
    }

    // ======================
    // RESET BUDIDAYA
    // ======================
    public function resetBudidaya()
    {
        $profile = TambakProfile::first();

        if (!$profile) {

            return response()->json([
                'error' => true
            ], 404);
        }

        // reset ke hari ini
        $profile->tanggal_mulai_budidaya =
            Carbon::now();

        $profile->save();

        return response()->json([
            'success' => true,
            'message' =>
                'Budidaya berhasil direset ke hari ini'
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

        $profile = TambakProfile::first();

        // kalau belum ada data
        if (!$profile) {

            $profile = new TambakProfile();

        }

        $profile->biomassa_udang =
            $request->biomassa_udang;

        $profile->save();

        return back()->with(
            'success',
            'Biomassa berhasil disimpan'
        );
    }
}