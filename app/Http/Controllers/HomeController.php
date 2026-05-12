<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeedingDaily;
use Carbon\Carbon;

class HomeController extends Controller
{
    // =====================
    // FUNCTION SHOW (HOME)
    // =====================
    public function show()
    {
        return view('home');
    }

    // =====================
    // FUNCTION KIRIM
    // =====================
    public function kirim(Request $request)
    {
        $today = Carbon::today()->toDateString();

        $data = FeedingDaily::firstOrCreate([
            'tanggal' => $today
        ]);

        $type = $request->type;
        $value = $request->value;

        // lanjut logic kamu di sini
    }

} // 👈 INI WAJIB ADA (penutup class)