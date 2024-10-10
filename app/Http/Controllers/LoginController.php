<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('auth.index');
    }

    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required',
        ]);

        $user = User::where($request->only('name', 'password'))->first();

        if ($user) {
            auth()->login($user);
            return redirect()->route('dashboard')->withSuccess('Login success.');
        } else {
            return redirect()->back()->withInput()->withError('Invalid Credentials !!!');
        }
    }
}
