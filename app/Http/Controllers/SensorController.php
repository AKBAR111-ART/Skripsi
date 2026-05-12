<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SensorController extends Controller
{
    public function store(Request $request)
    {
        try {

            DB::table('sensors')->insert([
                'ph' => $request->ph,
                'turbidity' => $request->turbidity,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'INSERT BERHASIL',
                'ph' => $request->ph,
                'turbidity' => $request->turbidity
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'ERROR DATABASE',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function latest()
    {
        return DB::table('sensors')
            ->latest('id')
            ->first();
    }
}