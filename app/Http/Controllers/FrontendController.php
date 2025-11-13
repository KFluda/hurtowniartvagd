<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class FrontendController extends Controller
{
    public function home()
    {
        return view('frontend.home');

    }
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $produkty = DB::table('produkty')
            ->select(
                'id_produktu',
                'nazwa',
                'kod_sku',
                'stawka_vat',
                DB::raw('COALESCE(cena_netto, 0) as cena_netto'),
                DB::raw('ROUND( COALESCE(cena_netto,0) * (1 + COALESCE(stawka_vat, 23) / 100), 2 ) as cena_brutto')
            )
            ->when($q, function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('nazwa', 'like', "%{$q}%")
                        ->orWhere('kod_sku', 'like', "%{$q}%");
                });
            })
            ->orderBy('nazwa')
            ->paginate(12)
            ->withQueryString();


        return view('frontend.index', compact('produkty', 'q'));
    }
    public function submitOrder(Request $request)
    {
        $data = $request->validate([
            'nazwa' => 'required|string|max:255',
            'email' => 'required|email',
            'telefon' => 'nullable|string|max:50',
            'wiadomosc' => 'nullable|string',
            'produkt_id' => 'required|integer|exists:produkty,id_produktu',
        ]);
        $produkt = DB::table('produkty')->find($data['produkt_id']);
        Mail::raw("Zapytanie o produkt: {$produkt->nazwa}\nOd: {$data['nazwa']} ({$data['email']})\nTelefon: {$data['telefon']}\nWiadomość: {$data['wiadomosc']}", function($m) {
            $m->to('sprzedaz@hurtownia.local')->subject('Nowe zapytanie od klienta');
        });

        return redirect()->route('home')->with('success', 'Dziękujemy! Twoje zapytanie zostało wysłane.');

    }



}
