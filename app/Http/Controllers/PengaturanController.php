<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengaturanTambak;


class PengaturanController extends Controller
{
    // =========================
    // HALAMAN PENGATURAN
    // =========================
public function index()
{
    $pengaturan = PengaturanTambak::first();

    if (!$pengaturan) {

        $pengaturan = PengaturanTambak::create([
            'whatsapp_aktif' => true,
            'rule_engine_aktif' => true,
            'penjaga' => [],
            'nomor_wa' => [],
            'waktu' => []
        ]);
    }

    $rule = \App\Models\PengaturanTambak::where('id', 1)->first();

    if (!$rule) {

        $rule = [
            'ph_min_good' => 7.5,
            'ph_max_good' => 8.5,

            'ph_min_warning' => 7.0,
            'ph_max_warning' => 7.4,

            'ph_danger_low' => 6.0,
            'ph_danger_high' => 9.0,

            'turbidity_min_good' => 10,
            'turbidity_max_good' => 50,

            'turbidity_min_warning' => 51,
            'turbidity_max_warning' => 70,

            'turbidity_danger_low' => 10,
            'turbidity_danger_high' => 15,
        ];
    } else {

        $rule = $rule->toArray();
    }

    return view('dashboard.pengaturan', compact(
        'pengaturan',
        'rule'
    ));
}

    // =========================
    // SIMPAN PENGATURAN UMUM
    // =========================
    public function store(Request $request)
    {
        $data = $request->pengingat ?? [];

        $pengaturan = PengaturanTambak::firstOrCreate(['id' => 1]);

        $pengaturan->update([
            'penjaga' => $data['penjaga'] ?? [],
            'nomor_wa' => $data['wa'] ?? [],
            'waktu' => $data['waktu'] ?? [],
            'tanggal' => $data['tanggal'] ?? null,
            'template_pesan' => $data['template_pesan'] ?? null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Berhasil disimpan'
        ]);
    }

    // =========================
    // UPDATE RULE ENGINE (FIXED)
    // =========================
public function updateRule(Request $request)
{
    $rule = PengaturanTambak::first();

    if (!$rule) {
        $rule = new PengaturanTambak();
    }

    $rule->fill([

        'ph_min_good' => $request->ph_min_good,
        'ph_max_good' => $request->ph_max_good,

        'ph_min_warning' => $request->ph_min_warning,
        'ph_max_warning' => $request->ph_max_warning,

        'ph_danger_low' => trim($request->ph_danger_low),
        'ph_danger_high' => trim($request->ph_danger_high),

        'turbidity_min_good' => $request->turbidity_min_good,
        'turbidity_max_good' => $request->turbidity_max_good,

        'turbidity_min_warning' => $request->turbidity_min_warning,
        'turbidity_max_warning' => $request->turbidity_max_warning,

        'turbidity_danger_low' => trim($request->turbidity_danger_low),
        'turbidity_danger_high' => trim($request->turbidity_danger_high),

        'status' => true
    ]);

    $rule->save();

    return response()->json([
        'status' => true,
        'message' => 'Rule berhasil disimpan'
    ]);
}
}
