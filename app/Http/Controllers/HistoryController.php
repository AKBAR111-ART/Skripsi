<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index2()
    {
         return view('dashboard.history');
    }
}
