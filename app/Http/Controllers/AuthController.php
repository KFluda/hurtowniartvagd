<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string','min:6'],
        ]);

        if (Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
            'aktywny' => 1,
        ])) {
            $request->session()->regenerate();
            return redirect()->intended(route('produkty.index'));
        }

        return back()->withErrors(['email' => 'Błędny e-mail/hasło lub konto nieaktywne.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
