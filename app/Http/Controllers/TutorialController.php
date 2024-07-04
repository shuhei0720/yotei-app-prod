<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TutorialController extends Controller
{
    public function show()
    {
        return view('tutorial');
    }

    public function complete(Request $request)
    {
        $user = Auth::user();
        $user->is_first_login = false;
        $user->save();

        return redirect()->route('dashboard');
    }
}