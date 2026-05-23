<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RuleSensorAir;
class RuleController extends Controller
{
    public function getRule()
{
    return response()->json(
        RuleSensorAir::first()
    );
}
}
