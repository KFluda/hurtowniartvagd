<?php

namespace App\Http\Controllers;
use App\Models\User;
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
    public function showRegisterForm()
    {
        return view('auth.register');
    }
    public function register(Request $request)
    {
        $request->validate([
            'imie_nazwisko' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:uzytkownicy,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $user = User::create([
            'imie_nazwisko' => $request->imie_nazwisko,
            'email' => $request->email,
            'haslo' => Hash::make($request->password),
            'rola' => 'KLIENT',
            'aktywny' => 1,
            'data_utworzenia' => now(),
        ]);
        Auth::login($user);

        return redirect()
            ->route('home')
            ->with('success','Konto zostało utworzone.');


    }



    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
