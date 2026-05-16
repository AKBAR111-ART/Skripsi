<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengingatHarian;

class PengingatController extends Controller
{
    
    public function store(Request $request)
{
    $request->validate([
        'nama_penjaga' => 'required',
        'tanggal' => 'required',
        'nomor_wa' => 'required',
        'jam' => 'required|array|min:3|max:3',
        'pesan' => 'required|array|min:3|max:3',
    ]);

    $pengingat = PengingatHarian::create([
        'nama_penjaga' => $request->nama_penjaga,
        'tanggal' => $request->tanggal,
        'nomor_wa' => $request->nomor_wa,
    ]);

    foreach ($request->jam as $i => $jam) {
        $pengingat->jadwal()->create([
            'jam' => $jam,
            'pesan' => $request->pesan[$i] ?? 'Waktunya makan udang'
        ]);
    }

    return back()->with('success', 'Pengingat berhasil disimpan');
}
}