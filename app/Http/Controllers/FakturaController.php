<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class FakturaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Lista zamówień (źródło faktur) */
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q',''));

        $zamowienia = DB::table('zamowienia as z')
            ->leftJoin('klienci as k','k.id_klienta','=','z.id_klienta')
            ->select('z.id_zamowienia','z.numer_zamowienia','z.data_wystawienia',
                'z.suma_netto','z.suma_vat','z.suma_brutto','k.nazwa as klient')
            ->when($q, function ($qr) use ($q) {
                $qr->where(function($w) use ($q){
                    $w->where('z.numer_zamowienia','like',"%$q%")
                        ->orWhere('k.nazwa','like',"%$q%");
                });
            })
            ->orderByDesc('z.id_zamowienia')
            ->paginate(20)
            ->withQueryString();

        return view('faktury.index', compact('zamowienia','q'));
    }

    /** Generowanie jednej faktury VAT w PDF na podstawie zamówienia */
    public function pdf($id)
    {
        $zam = DB::table('zamowienia as z')
            ->leftJoin('klienci as k','k.id_klienta','=','z.id_klienta')
            ->select('z.*','k.nazwa as klient','k.nip','k.miasto','k.ulica','k.kod_pocztowy','k.email','k.telefon')
            ->where('z.id_zamowienia',$id)
            ->first();

        if (!$zam) abort(404);

        $pozycje = DB::table('zamowienia_pozycje')
            ->where('id_zamowienia',$id)
            ->orderBy('id_pozycji')
            ->get();

        // Numer faktury – prosty schemat na bazie roku i ID zamówienia:
        $nrFaktury = 'FV/'.date('Y').'/'.str_pad($zam->id_zamowienia, 5, '0', STR_PAD_LEFT);

        $sprzedawca = config('firma');

        $pdf = Pdf::loadView('faktury.vat', [
            'nr'       => $nrFaktury,
            'zam'      => $zam,
            'pozycje'  => $pozycje,
            'sprzedawca' => $sprzedawca,
        ])->setPaper('a4');

        $filename = 'Faktura_'.$nrFaktury.'.pdf';
        return $pdf->download($filename);  // albo ->stream($filename);
    }
}
