<?php

namespace App\Http\Controllers;
use App\Models\Zamowienie;
use Illuminate\Support\Str;
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
    // app/Http/Controllers/FrontendController.php

    public function index(Request $request)
    {
        // 1) Parametry z URL
        $q         = trim((string) $request->query('q', ''));
        $sort      = $request->query('sort', '');
        $kategoria = $request->query('kategoria', '');
        $producent = $request->query('producent', '');
        $cena_min  = $request->query('cena_min', '');
        $cena_max  = $request->query('cena_max', '');
        $seryjny   = $request->query('seryjny', '');   // 0 / 1 / ''

        // 2) Bazowe zapytanie o produkty (tylko aktywne)
        $query = DB::table('produkty as p')
            ->leftJoin('kategorie as k', 'k.id_kategorii', '=', 'p.id_kategorii')
            ->leftJoin('producenci as pr', 'pr.id_producenta', '=', 'p.id_producenta')
            ->where('p.aktywny', 1)
            ->select(
                'p.id_produktu',
                'p.nazwa',
                'p.kod_sku',
                'p.id_kategorii',
                'p.id_producenta',
                'p.cena_netto',
                'p.stawka_vat',
                'p.czy_z_numerem_seryjnym',
                DB::raw('ROUND(COALESCE(p.cena_netto,0) * (1 + COALESCE(p.stawka_vat,0) / 100), 2) AS cena_brutto'),
                'k.nazwa as kategoria_nazwa',
                'pr.nazwa as producent_nazwa'
            );

        // 3) Wyszukiwanie po nazwie / SKU
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('p.nazwa', 'like', "%{$q}%")
                    ->orWhere('p.kod_sku', 'like', "%{$q}%");
            });
        }

        // 4) Filtr: kategoria
        if ($kategoria !== '') {
            $query->where('p.id_kategorii', (int) $kategoria);
        }

        // 5) Filtr: producent
        if ($producent !== '') {
            $query->where('p.id_producenta', (int) $producent);
        }

        // 6) Filtr: czy z numerem seryjnym
        if ($seryjny === '0' || $seryjny === '1') {
            $query->where('p.czy_z_numerem_seryjnym', (int) $seryjny);
        }

        // 7) Filtr: cena (po BRUTTO – to co widzi klient)
        if ($cena_min !== '' && is_numeric(str_replace(',', '.', $cena_min))) {
            $cenaMin = (float) str_replace(',', '.', $cena_min);
            $query->whereRaw(
                'COALESCE(p.cena_netto,0) * (1 + COALESCE(p.stawka_vat,0) / 100) >= ?',
                [$cenaMin]
            );
        }

        if ($cena_max !== '' && is_numeric(str_replace(',', '.', $cena_max))) {
            $cenaMax = (float) str_replace(',', '.', $cena_max);
            $query->whereRaw(
                'COALESCE(p.cena_netto,0) * (1 + COALESCE(p.stawka_vat,0) / 100) <= ?',
                [$cenaMax]
            );
        }

        // 8) Sortowanie
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('cena_brutto', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('cena_brutto', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('p.nazwa', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('p.nazwa', 'desc');
                break;
            case 'newest':
                $query->orderBy('p.data_utworzenia', 'desc');
                break;
            default:
                // domyślnie po nazwie
                $query->orderBy('p.nazwa', 'asc');
                break;
        }

        // 9) Paginacja – zachowuje parametry filtrów w URL
        $produkty = $query->paginate(12)->withQueryString();

        // 10) Listy kategorii i producentów do selectów
        $kategorie = DB::table('kategorie')
            ->select('id_kategorii', 'nazwa')
            ->orderBy('nazwa')
            ->get();

        $producenci = DB::table('producenci')
            ->select('id_producenta', 'nazwa')
            ->orderBy('nazwa')
            ->get();

        // 11) Widok
        return view('frontend.index', [
            'produkty'   => $produkty,
            'q'          => $q,
            'sort'       => $sort,
            'kategoria'  => $kategoria,
            'producent'  => $producent,
            'cena_min'   => $cena_min,
            'cena_max'   => $cena_max,
            'seryjny'    => $seryjny,
            'kategorie'  => $kategorie,
            'producenci' => $producenci,
        ]);
    }
    public function konto()
    {
        $user = auth()->user();
        $email = $user->email;

        // Szukamy zamówień po mailu zapisanym w "uwagi"
        $zamowienia = Zamowienie::where('uwagi', 'like', '%(' . $email . ')%')
            ->orderByDesc('data_utworzenia')
            ->get();

        return view('frontend.konto', [
            'user'       => $user,
            'zamowienia' => $zamowienia,
        ]);
    }
    public function mojeZamowienie(int $id)
    {
        $user  = auth()->user();
        $email = $user->email;

        // Zamówienie musi "należeć" do tego maila
        $zamowienie = Zamowienie::where('id_zamowienia', $id)
            ->where('uwagi', 'like', '%(' . $email . ')%')
            ->firstOrFail();

        return view('frontend.konto-zamowienie', [
            'user'       => $user,
            'zamowienie' => $zamowienie,
        ]);
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

    /** Formularz kontaktowy (GET) */
    public function contactForm()
    {
        return view('pages.kontakt');
    }

    /** Obsługa wysłania wiadomości z formularza kontaktowego (POST) */
    public function contactSend(Request $request)
    {
        $data = $request->validate([
            'imie_nazwisko' => 'required|string|max:255',
            'email'         => 'required|email',
            'firma'         => 'nullable|string|max:255',
            'temat'         => 'required|string|max:255',
            'wiadomosc'     => 'required|string|min:10',
        ]);

        $body = "Nowa wiadomość z formularza kontaktowego:\n\n"
            . "Imię i nazwisko: {$data['imie_nazwisko']}\n"
            . "E-mail: {$data['email']}\n"
            . "Firma: " . ($data['firma'] ?? '-') . "\n"
            . "Temat: {$data['temat']}\n\n"
            . "Treść wiadomości:\n{$data['wiadomosc']}";

        Mail::raw($body, function ($message) {
            $message->to('kontakt@hurtownia.local')
                ->subject('Nowa wiadomość z formularza kontaktowego');
        });

        return back()->with('success', 'Dziękujemy! Twoja wiadomość została wysłana.');
    }

    /** Widok koszyka */
    public function cart()
    {
        // koszyk trzymamy w sesji pod kluczem 'cart'
        // struktura: [id_produktu => ['id_produktu'=>..., 'nazwa'=>..., 'kod_sku'=>..., 'ilosc'=>..., 'cena_brutto'=>...], ...]
        $cart = session('cart', []);

        $pozycje = collect($cart);

        $sumaBrutto = $pozycje->sum(function ($item) {
            $ilosc = (float)($item['ilosc'] ?? 0);
            $cena  = (float)($item['cena_brutto'] ?? 0);
            return $ilosc * $cena;
        });

        return view('frontend.cart', [
            'pozycje'    => $pozycje,
            'sumaBrutto' => $sumaBrutto,
        ]);
    }

    /** Dodanie produktu do koszyka */
    public function addToCart(Request $request)
    {
        $request->validate([
            'produkt_id' => 'required|integer|exists:produkty,id_produktu',
        ]);

        $produkt = DB::table('produkty')
            ->select(
                'id_produktu',
                'nazwa',
                'kod_sku',
                'cena_netto',
                'stawka_vat',
                DB::raw('ROUND(COALESCE(cena_netto,0) * (1 + COALESCE(stawka_vat,0) / 100), 2) as cena_brutto')
            )
            ->where('id_produktu', $request->produkt_id)
            ->first();

        if (! $produkt) {
            return back()->with('error', 'Nie znaleziono produktu.');
        }

        // pobierz aktualny koszyk
        $cart = session()->get('cart', []);

        // jeżeli już jest w koszyku → zwiększ ilość
        if (isset($cart[$produkt->id_produktu])) {
            $cart[$produkt->id_produktu]['ilosc'] += 1;
        } else {
            // dodaj nową pozycję
            $cart[$produkt->id_produktu] = [
                'id_produktu' => $produkt->id_produktu,
                'nazwa'       => $produkt->nazwa,
                'kod_sku'     => $produkt->kod_sku,
                'ilosc'       => 1,
                'cena_brutto' => (float)$produkt->cena_brutto,
            ];
        }

        session()->put('cart', $cart);

        return back()->with('success', 'Produkt dodany do koszyka.');
    }

    /** Zmiana ilości w koszyku */
    public function updateCart(Request $request)
    {
        $data = $request->validate([
            'produkt_id' => 'required|integer',
            'ilosc'      => 'required|integer|min:1',
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$data['produkt_id']])) {
            $cart[$data['produkt_id']]['ilosc'] = (int)$data['ilosc'];
            session()->put('cart', $cart);
        }

        return back()->with('success', 'Koszyk zaktualizowany.');
    }

    /** Usunięcie pozycji z koszyka */
    public function removeFromCart(Request $request)
    {
        $data = $request->validate([
            'produkt_id' => 'required|integer',
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$data['produkt_id']])) {
            unset($cart[$data['produkt_id']]);
            session()->put('cart', $cart);
        }

        return back()->with('success', 'Produkt usunięty z koszyka.');
    }
    public function blikForm()
    {
        $suma = collect(session('cart', []))
            ->sum(fn($p) => $p['ilosc'] * $p['cena_brutto']);

        return view('frontend.platnosc-blik', [
            'suma' => $suma
        ]);
    }

    public function blikPay(Request $request)
    {
        // 1. Walidacja formularza
        $rules = [
            'imie_nazwisko'  => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255'],
            'ulica'          => ['required', 'string', 'max:255'],
            'miasto'         => ['required', 'string', 'max:255'],
            'kod_pocztowy'   => ['required', 'regex:/^[0-9]{2}-[0-9]{3}$/'],
            'dostawa'        => ['required', 'in:kurier,odbior'],
            'platnosc'       => ['required', 'in:blik,przelew,pobranie'],
            'distance'       => ['required', 'integer', 'min:1', 'max:500'],
        ];

        if ($request->platnosc === 'blik') {
            $rules['blik'] = ['required', 'regex:/^[0-9]{6}$/'];
        }

        $validated = $request->validate($rules);

        // 2. Koszyk z sesji
        $cart = collect(session('cart', []));

        if ($cart->isEmpty()) {
            return redirect()
                ->route('koszyk')
                ->with('error', 'Koszyk jest pusty. Dodaj produkty przed złożeniem zamówienia.');
        }

        $sumaBrutto = $cart->sum(function ($item) {
            $ilosc = (int)($item['ilosc'] ?? 0);
            $cena  = (float)($item['cena_brutto'] ?? 0);
            return $ilosc * $cena;
        });

        // 3. Obliczenia
        $distance      = (int)$validated['distance'];
        $dniDostawy    = $this->obliczDniDostawy($distance);
        [$netto, $vat] = $this->przeliczNettoVat($sumaBrutto);
        $numer         = $this->generujNumerZamowienia();
        $now           = now();

        // 4. Id klienta – tu zależy jak masz powiązane user/klient.
        // Jeśli kolumna id_klienta może być NULL, zostaw jak poniżej.
        $domyslnyKlientId = 1;

// jeżeli masz logowanie i user jest powiązany z klientem:
        if (auth()->check() && isset(auth()->user()->id_klienta)) {
            $idKlienta = auth()->user()->id_klienta;
        } else {
            $idKlienta = $domyslnyKlientId;
        }

        // 5. Zbudujemy opis w "uwagi", bo tabela nie ma osobnych kolumn na adres
        $uwagi = "Odbiorca: {$validated['imie_nazwisko']} ({$validated['email']}), "
            . "Adres: {$validated['ulica']}, {$validated['kod_pocztowy']} {$validated['miasto']}. "
            . "Dostawa: {$validated['dostawa']}, płatność: {$validated['platnosc']}, "
            . "odległość: {$distance} km, szacowany czas dostawy: {$dniDostawy} dni.";

        // 6. Zapis zamówienia do tabeli `zamowienia`
        $zamowienie = Zamowienie::create([
            'numer_zamowienia' => $numer,
            'id_klienta'       => $idKlienta,                 // jeśli kolumna ma NOT NULL, wpisz np. 0
            'data_wystawienia' => $now->toDateString(),
            'status'           => $this->statusStartowy($validated['platnosc']),
            'suma_netto'       => $netto,
            'suma_vat'         => $vat,
            'suma_brutto'      => $sumaBrutto,
            'uwagi'            => $uwagi,
            'data_utworzenia'  => $now,
        ]);

        // 7. (na razie) nie zapisujemy pozycji – to można dorobić do osobnej tabeli
        // jeśli taką masz, dopisz create() w pętli po $cart.

        // 8. Czyścimy koszyk
        session()->forget('cart');

        // 9. Komunikat dla klienta
        return redirect()
            ->route('sklep')
            ->with('success', 'Zamówienie zostało złożone. Numer zamówienia: ' . $numer);
    }

    private function obliczDniDostawy(int $distance): int
    {
        if($distance <=50){
            return 1;
        }
        if($distance <=200) {
            return 2;
        }
        return 2 + (int)ceil(($distance - 200) / 200);

    }
    private function generujNumerZamowienia(): string
    {
        $date = now()->format('Ymd');

        do {
            $suffix = Str::upper(Str::random(5));
            $number = "ZAM-{$date}-{$suffix}";
        } while (Zamowienie::where('numer_zamowienia', $number)->exists());

        return $number;
    }
    private function przeliczNettoVat(float $brutto): array
    {
        $netto = round($brutto / 1,23, 2);
        $vat = round($brutto - $netto, 2);
        return [$netto, $vat];

    }
    private function statusStartowy(string $platnosc): string
    {
        return match ($platnosc) {
            'przelew'  => 'oczekuje na płatność',
            default => 'zarejestrowane',
        };
    }







}
