<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class FrontendController extends Controller
{
    /** Strona główna */
    public function home()
    {
        return view('frontend.home');
    }

    /** Sklep – lista produktów */
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $produkty = DB::table('produkty')
            ->select(
                'id_produktu',
                'nazwa',
                'kod_sku',
                'cena_netto',
                'stawka_vat',
                // wyliczamy cenę brutto z kolumn cena_netto + stawka_vat
                DB::raw('ROUND(COALESCE(cena_netto,0) * (1 + COALESCE(stawka_vat,0) / 100), 2) as cena_brutto')
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

    /** Szczegóły produktu */
    public function show($id)
    {
        $produkt = DB::table('produkty')
            ->select(
                'id_produktu',
                'nazwa',
                'kod_sku',
                'cena_netto',
                'stawka_vat',
                DB::raw('ROUND(COALESCE(cena_netto,0) * (1 + COALESCE(stawka_vat,0) / 100), 2) as cena_brutto')
            )
            ->where('id_produktu', $id)
            ->first();

        if (! $produkt) {
            abort(404);
        }

        return view('frontend.show', compact('produkt'));
    }

    /** Formularz zapytania o produkt */
    public function submitOrder(Request $request)
    {
        $data = $request->validate([
            'nazwa'       => 'required|string|max:255',
            'email'       => 'required|email',
            'telefon'     => 'nullable|string|max:50',
            'wiadomosc'   => 'nullable|string',
            'produkt_id'  => 'required|integer|exists:produkty,id_produktu',
        ]);

        $produkt = DB::table('produkty')
            ->select('id_produktu', 'nazwa', 'kod_sku')
            ->where('id_produktu', $data['produkt_id'])
            ->first();

        if (! $produkt) {
            abort(404);
        }

        $body = "Zapytanie o produkt:\n"
            . "ID: {$produkt->id_produktu}\n"
            . "Nazwa: {$produkt->nazwa}\n"
            . "Kod SKU: {$produkt->kod_sku}\n\n"
            . "Od: {$data['nazwa']} ({$data['email']})\n"
            . "Telefon: " . ($data['telefon'] ?? '-') . "\n\n"
            . "Wiadomość:\n{$data['wiadomosc']}";

        Mail::raw($body, function ($message) {
            $message->to('sprzedaz@hurtownia.local')
                ->subject('Nowe zapytanie od klienta ze strony www');
        });

        return redirect()
            ->route('home')
            ->with('success', 'Dziękujemy! Twoje zapytanie zostało wysłane.');
    }

    public function contactForm()
    {
        return view('pages.kontakt');
    }

    /**
     * Obsługa wysłania wiadomości z formularza kontaktowego (POST)
     */
    public function contactSend(Request $request)
    {
        $data = $request->validate([
            'imie_nazwisko' => 'required|string|max:255',
            'email'         => 'required|email',
            'firma'         => 'nullable|string|max:255',
            'temat'         => 'required|string|max:255',
            'wiadomosc'     => 'required|string|min:10',
        ]);

        // Treść maila
        $body = "Nowa wiadomość z formularza kontaktowego:\n\n"
            . "Imię i nazwisko: {$data['imie_nazwisko']}\n"
            . "E-mail: {$data['email']}\n"
            . "Firma: " . ($data['firma'] ?? '-') . "\n"
            . "Temat: {$data['temat']}\n\n"
            . "Treść wiadomości:\n{$data['wiadomosc']}";

        // Wysłanie maila (adres docelowy ustaw jaki chcesz)
        Mail::raw($body, function ($message) {
            $message->to('kontakt@hurtownia.local')
                ->subject('Nowa wiadomość z formularza kontaktowego');
        });

        return back()->with('success', 'Dziękujemy! Twoja wiadomość została wysłana.');
    }

    public function cart()
    {
        // Zakładam, że trzymasz koszyk w sesji pod kluczem 'cart'
        // np. ['id_produktu' => ['id_produktu'=>..., 'nazwa'=>..., 'kod_sku'=>..., 'ilosc'=>..., 'cena_brutto'=>...], ...]
        $cart = session('cart', []);

        // Zamieniamy na kolekcję, będzie wygodniej
        $pozycje = collect($cart);

        $sumaBrutto = $pozycje->sum(function ($item) {
            $ilosc = (float)($item['ilosc'] ?? 0);
            $cena  = (float)($item['cena_brutto'] ?? 0);
            return $ilosc * $cena;
        });

        return view('frontend.cart', [
            'pozycje'     => $pozycje,
            'sumaBrutto'  => $sumaBrutto,
        ]);
    }


}
