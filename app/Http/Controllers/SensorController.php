<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengaturanTambak;

class SensorController extends Controller
{
    public function realtime()
    {
        // =========================
        // AMBIL RULE DATABASE
        // =========================

        $rule = PengaturanTambak::first();

        // =========================
        // DATA SENSOR SEMENTARA
        // NANTI DARI ESP32
        // =========================

        $ph = 7.2;
        $turbidity = 12;

        // =========================
        // CEK PH
        // =========================

        if (
            $ph >= $rule->ph_min_good &&
            $ph <= $rule->ph_max_good
        ) {

            $statusPh = 'baik';

        } elseif (

            $ph >= $rule->ph_min_warning &&
            $ph <= $rule->ph_max_warning

        ) {

            $statusPh = 'warning';

        } else {

            $statusPh = 'bahaya';
        }

        // =========================
        // CEK TURBIDITY
        // =========================

        if (
            $turbidity >= $rule->turbidity_min_good &&
            $turbidity <= $rule->turbidity_max_good
        ) {

            $statusTur = 'baik';

        } elseif (

            $turbidity >= $rule->turbidity_min_warning &&
            $turbidity <= $rule->turbidity_max_warning

        ) {

            $statusTur = 'warning';

        } else {

            $statusTur = 'bahaya';
        }

        // =========================
        // RESPONSE JSON
        // =========================

        return response()->json([

            'ph' => $ph,
            'ph_status' => $statusPh,

            'turbidity' => $turbidity,
            'turbidity_status' => $statusTur,

        ]);
    }
}