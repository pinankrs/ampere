<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function logout()
    {
        Auth::logout();
        return redirect()->route('auth.show-login');
    }

}
