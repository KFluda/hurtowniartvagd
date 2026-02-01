<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ====== LOGOWANIE ======
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // szukamy w tabeli uzytkownicy po email + aktywny
        $user = User::where('email', $data['email'])
            ->where('aktywny', 1)
            ->first();

        // hasło jest w kolumnie: haslo (bcrypt)
        if (!$user || !Hash::check($data['password'], $user->haslo)) {
            return back()
                ->withErrors(['email' => 'Nieprawidłowy login lub hasło'])
                ->withInput();
        }

        Auth::login($user);
        $request->session()->regenerate();

        // ====== PRZEKIEROWANIE WG ROLI ======
        $rola = strtoupper((string)($user->rola ?? ''));

        // Klient ma trafić na stronę główną (albo sklep)
        if ($rola === 'KLIENT') {
            return redirect()->intended(route('home')); // albo route('sklep')
        }

        // Admin/Kierownik/Pracownik -> panel
        return redirect()->intended(route('panel'));
    }

    // ====== REJESTRACJA ======
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'imie_nazwisko' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:uzytkownicy,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = \App\Models\User::create([
            'imie_nazwisko' => $data['imie_nazwisko'],
            'nazwa'         => $data['imie_nazwisko'],
            'email'         => $data['email'],
            'haslo'         => \Illuminate\Support\Facades\Hash::make($data['password']),
            'rola'          => 'KLIENT',
            'aktywny'       => 1,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        \Illuminate\Support\Facades\Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success','Konto zostało utworzone.');
    }

    // ====== WYLOGOWANIE ======
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
