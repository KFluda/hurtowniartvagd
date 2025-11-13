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
        $validated = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string','min:6'],
        ]);
        $user = DB::table('uzytkownicy')
            ->where('email',$validated['email'])
            ->where('aktywny',1)
            ->first();
        if(!$user || !Hash::check($validated['password'], $user->haslo))
        {
            return back ()
                ->withErrors(['email' => 'Nieprawidłowy login lub hasło'])
                ->withInput();

        }
        Auth::loginUsingId($user->id_uzytkownika);
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
