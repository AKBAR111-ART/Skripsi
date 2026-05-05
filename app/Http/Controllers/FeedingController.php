<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeedingDaily;

class FeedingController extends Controller
{
    public function update(Request $request, $shift)
    {
        $data = FeedingDaily::firstOrCreate([
            'tanggal' => now()->toDateString()
        ]);

        $rekom = ($request->biomassa * 0.05) + (7 - $request->ph) + ($request->turbidity * 0.1);

        $map = [
            'pagi' => ['rekomendasi_pagi','real_pakan_pagi'],
            'siang' => ['rekomendasi_siang','real_pakan_siang'],
            'sore' => ['rekomendasi_sore','real_pakan_sore'],
            'malam' => ['rekomendasi_malam','real_pakan_malam'],
        ];

        $data->update([
            'ph' => $request->ph,
            'turbidity' => $request->turbidity,
            'biomassa' => $request->biomassa,

            $map[$shift][0] => $rekom,
            $map[$shift][1] => $request->real_pakan,
        ]);

        return response()->json([
            'status' => 'ok',
            'rekomendasi' => $rekom
        ]);
    }
}
