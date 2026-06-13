<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function home()
    {
        $user = User::find(session('user_id'));
        
        if (!$user) {
            return redirect('/');
        }

        // Safe get pakan hari ini (dengan pengecekan tabel)
        $pakanHariIni = 0;
        if (Schema::hasTable('feeding_records')) {
            try {
                $pakanHariIni = DB::table('feeding_records')
                    ->whereDate('feeding_time', today())
                    ->where('user_id', $user->id)
                    ->sum('pakan_kg');
            } catch (\Exception $e) {
                $pakanHariIni = 0;
            }
        }

        return view('dashboard.home', [
            'pakanHariIni' => $pakanHariIni,
            'populasi' => $user->populasi ?? 0,
            'berat_rata' => $user->berat_rata ?? 0,
            'biomassa' => (($user->populasi ?? 0) * ($user->berat_rata ?? 0)) / 1000,
            'umur_minggu' => $user->tebar_date ? now()->diffInWeeks($user->tebar_date) : 0,
            'target_panen_kg' => $user->target_panen_kg ?? 0,
            'target_size_gram' => $user->target_size_gram ?? 0,
            'rule' => null,
        ]);
    }
}