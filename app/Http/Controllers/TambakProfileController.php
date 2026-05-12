<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TambakProfile;

class ProfileController extends Controller
{
    public function index5()
    {
        $profile = TambakProfile::first();

        return view('dashboard.profile', compact('profile'));
    }

    public function update(Request $request)
    {
        $profile = TambakProfile::first();

        if (!$profile) {
            $profile = new TambakProfile();
        }

        $profile->fill($request->all());
        $profile->save();

        return redirect()->back()->with('success', 'Profil berhasil diupdate');
    }
}