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
        $q = trim((string) $request->query('q', ''));

        $zamowienia = DB::table('zamowienia as z')
            ->leftJoin('klienci as k', 'k.id_klienta', '=', 'z.id_klienta')
            ->select(
                'z.id_zamowienia',
                'z.numer_zamowienia',
                'z.data_wystawienia',
                'z.suma_netto',     // ⬅️ DODANE
                'z.suma_vat',       // ⬅️ DODANE
                'z.suma_brutto',
                'z.status',
                'k.nazwa as klient'
            )
            ->where('z.status', 'zrealizowane')       // tylko zrealizowane (jak chciałeś)
            ->when($q, function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('z.numer_zamowienia', 'like', "%$q%")
                        ->orWhere('k.nazwa', 'like', "%$q%");
                });
            })
            ->orderByDesc('z.data_wystawienia')
            ->paginate(20)
            ->withQueryString();

        return view('faktury.index', compact('zamowienia', 'q'));
    }




    /** Generowanie jednej faktury VAT w PDF na podstawie zamówienia */
    public function pdf($id)
    {
        $zam = DB::table('zamowienia as z')
            ->leftJoin('klienci as k', 'k.id_klienta', '=', 'z.id_klienta')
            ->select('z.*', 'k.nazwa as klient', 'k.nip', 'k.miasto', 'k.ulica', 'k.kod_pocztowy', 'k.email', 'k.telefon')
            ->where('z.id_zamowienia', $id)
            ->first();

        if (!$zam) abort(404);

        // ❗ twarda blokada – tylko zrealizowane
        if ($zam->status !== 'zrealizowane') {
            return redirect()
                ->route('faktury.index')
                ->with('error', 'Fakturę można wygenerować tylko dla zamówienia w statusie „zrealizowane”.');
        }

        $pozycje = DB::table('zamowienia_pozycje')
            ->where('id_zamowienia', $id)
            ->orderBy('id_pozycji')
            ->get();

        // numer faktury wg Twojej logiki…
        $nrFaktury = 'FV/'.date('Y').'/'.$zam->id_zamowienia;

        $pdf = \PDF::loadView('faktury.vat', [
            'zam'       => $zam,
            'pozycje'   => $pozycje,
            'nrFaktury' => $nrFaktury,
        ])->setPaper('A4');

        return $pdf->download("Faktura_{$nrFaktury}.pdf");
    }

}
