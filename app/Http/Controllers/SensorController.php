<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sensor;

class SensorController extends Controller
{
    // ESP32 kirim data ke sini
    public function store(Request $request)
    {
        $data = Sensor::create([
            'ph' => $request->ph,
            'turbidity' => $request->turbidity
        ]);

        return response()->json([
            'message' => 'OK masuk database',
            'data' => $data
        ]);
    }

    // ambil data terbaru (dashboard nanti)
    public function latest()
    {
        return Sensor::latest()->first();
    }
}