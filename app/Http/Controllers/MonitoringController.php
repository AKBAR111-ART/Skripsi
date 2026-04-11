<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function index3()
    {
         return view('dashboard.monitoring');
    }
}
