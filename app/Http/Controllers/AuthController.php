<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = \App\Models\User::where('email', $request->email)
            ->where('aktywny', 1)
            ->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Nieprawidłowy login lub hasło'])->withInput();
        }

        // WAŻNE: hasło jest w kolumnie "haslo"
        if (!\Hash::check($request->password, $user->haslo)) {
            return back()->withErrors(['email' => 'Nieprawidłowy login lub hasło'])->withInput();
        }

        \Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('panel'));
    }


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
