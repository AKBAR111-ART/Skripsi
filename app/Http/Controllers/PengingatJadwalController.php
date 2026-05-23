<?php

namespace App\Http\Controllers;

use App\Models\PengingatJadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PengingatJadwalController extends Controller
{
    // GET: Ambil semua jadwal
    public function getJadwal()
    {
        try {
            $jadwal = PengingatJadwal::orderBy('jam')->get();
            
            return response()->json([
                'success' => true,
                'data' => $jadwal
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    // POST: Tambah jadwal
    public function store(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'jam' => 'required',
                'pesan' => 'required|string'
            ]);
            
            // Simpan ke database
            $jadwal = PengingatJadwal::create([
                'jam' => $request->jam,
                'pesan' => $request->pesan,
                'target_nomor' => $request->target_nomor ? json_encode($request->target_nomor) : null,
                'is_sent' => false
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil ditambahkan',
                'data' => $jadwal
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->errors())
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // DELETE: Hapus jadwal
    public function destroy($id)
    {
        try {
            $jadwal = PengingatJadwal::findOrFail($id);
            $jadwal->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Jadwal dihapus'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}