<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengaturanController extends Controller
{
    // =====================
    // TAMPIL HALAMAN
    // =====================
public function index()
{
    $data = DB::table('pengaturan_tambak')->latest()->first();

    $rule = $data ? json_decode($data->rule_sensor, true) : null;

    $pengingat = $data ? json_decode($data->pengingat, true) : null;

    return view('dashboard.pengaturan', compact('rule', 'pengingat'));
}

    // =====================
    // SIMPAN DATA RULE + PENGINGAT
    // =====================
public function store(Request $request)
{
    if (!$request->rule_sensor || empty($request->rule_sensor)) {
        return response()->json([
            'message' => 'rule_sensor kosong'
        ], 422);
    }

    DB::table('pengaturan_tambak')->insert([
        'rule_sensor' => json_encode($request->rule_sensor),
        'pengingat'   => json_encode($request->pengingat),
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);

    return response()->json([
        'message' => 'Berhasil disimpan'
    ], 200);
}

    // =====================
    // OPTIONAL: HISTORY
    // =====================
    public function history()
    {
        $data = DB::table('pengaturan_tambak')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pengaturan_history', compact('data'));
    }
}
