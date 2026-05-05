<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Sensor;

use App\Http\Controllers\FeedingController;

Route::post('/api/feeding/{shift}', [FeedingController::class, 'update']);
Route::post('/sensor', function (Request $request) {

    \App\Models\Sensor::updateOrCreate(
        ['id' => 1],
        [
            'ph' => $request->ph,
            'turbidity' => $request->turbidity
        ]
    );

    return response()->json(['status' => 'ok']);
});