<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $counts = [
            'produkty' => $this->safeCount('produkty'),
            'magazyny' => $this->safeCount('magazyny'),
            'klienci'  => $this->safeCount('klienci'),
            'dostawcy' => $this->safeCount('dostawcy'),
            'faktury'  => $this->safeCount('faktury_sprzedazy'),
        ];

        return view('uzytkownik.panel', [
            'user'   => Auth::user(),
            'counts' => $counts,
        ]);
    }

    private function safeCount($table)
    {
        try {
            return DB::table($table)->count();
        } catch (\Exception $e) {
            return null;
        }
    }
}
