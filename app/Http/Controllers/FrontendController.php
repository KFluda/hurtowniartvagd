<?php

namespace App\Http\Controllers;

use App\Models\Zamowienie;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class FrontendController extends Controller
{
    public function home()
    {
        return view('frontend.home');
    }

    public function index(Request $request)
    {
        $q         = trim((string) $request->query('q', ''));
        $sort      = $request->query('sort', '');
        $kategoria = $request->query('kategoria', '');
        $producent = $request->query('producent', '');
        $cena_min  = $request->query('cena_min', '');
        $cena_max  = $request->query('cena_max', '');
        $seryjny   = $request->query('seryjny', '');   // 0 / 1 / ''

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

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('p.nazwa', 'like', "%{$q}%")
                    ->orWhere('p.kod_sku', 'like', "%{$q}%");
            });
        }

        if ($kategoria !== '') {
            $query->where('p.id_kategorii', (int) $kategoria);
        }

        if ($producent !== '') {
            $query->where('p.id_producenta', (int) $producent);
        }

        if ($seryjny === '0' || $seryjny === '1') {
            $query->where('p.czy_z_numerem_seryjnym', (int) $seryjny);
        }

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
                $query->orderBy('p.nazwa', 'asc');
                break;
        }

        $produkty = $query->paginate(12)->withQueryString();

        $kategorie = DB::table('kategorie')
            ->select('id_kategorii', 'nazwa')
            ->orderBy('nazwa')
            ->get();

        $producenci = DB::table('producenci')
            ->select('id_producenta', 'nazwa')
            ->orderBy('nazwa')
            ->get();

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
        if (!$user) abort(403);

        $email = (string)($user->email ?? '');

        // Jeśli masz kolumnę id_uzytkownika w zamowienia → użyj jej.
        // Jak nie masz → filtruj po mailu w "uwagi" (format: (mail@...))
        if (Schema::hasColumn('zamowienia', 'id_uzytkownika')) {
            $zamowienia = Zamowienie::where('id_uzytkownika', $user->id_uzytkownika)
                ->orderByDesc('data_utworzenia')
                ->get();
        } else {
            $zamowienia = Zamowienie::where('uwagi', 'like', '%(' . $email . ')%')
                ->orderByDesc('data_utworzenia')
                ->get();
        }

        return view('frontend.konto', [
            'user'       => $user,
            'zamowienia' => $zamowienia,
        ]);
    }

    public function mojeZamowienie(int $id)
    {
        $user = auth()->user();
        if (!$user) abort(403);

        $email = (string)($user->email ?? '');

        if (Schema::hasColumn('zamowienia', 'id_uzytkownika')) {
            $zamowienie = Zamowienie::where('id_zamowienia', $id)
                ->where('id_uzytkownika', $user->id_uzytkownika)
                ->firstOrFail();
        } else {
            $zamowienie = Zamowienie::where('id_zamowienia', $id)
                ->where('uwagi', 'like', '%(' . $email . ')%')
                ->firstOrFail();
        }

        return view('frontend.konto-zamowienie', [
            'user'       => $user,
            'zamowienie' => $zamowienie,
        ]);
    }

    public function show($id)
    {
        $produkt = DB::table('produkty as p')
            ->leftJoin('producenci as pr', 'pr.id_producenta', '=', 'p.id_producenta')
            ->leftJoin('kategorie as k', 'k.id_kategorii', '=', 'p.id_kategorii')
            ->select(
                'p.*',
                'pr.nazwa as producent',
                'k.nazwa as kategoria',
                DB::raw('ROUND(COALESCE(p.cena_netto,0) * (1 + COALESCE(p.stawka_vat,0) / 100), 2) as cena_brutto')
            )
            ->where('p.id_produktu', $id)
            ->where('p.aktywny', 1)
            ->first();

        if (!$produkt) abort(404);

        // MAPA: kategoria -> plik w /public/images
        $categoryImages = [
            'Audio'         => 'audio.jpg',
            'AGD Kuchenne'  => 'ekspres.jpg',
            'Pralki'        => 'pralka.jpg',
            'Telewizory'    => 'telewizor.jpg',
            'Komputery'     => 'laptop.jpg',
        ];

        // 1) jeśli produkt ma własny obrazek w kolumnie image (i leży w /public/images)
        if (!empty($produkt->image)) {
            $produkt->image_url = asset('images/' . $produkt->image);
        }
        // 2) fallback po kategorii
        elseif (!empty($produkt->kategoria) && isset($categoryImages[$produkt->kategoria])) {
            $produkt->image_url = asset('images/' . $categoryImages[$produkt->kategoria]);
        }
        // 3) ostatecznie placeholder z /public/images
        else {
            $produkt->image_url = asset('images/placeholder-product.png'); // u Ciebie na screenie jest placeholder-product.png
        }

        return view('frontend.produkt', compact('produkt'));
    }

    /** Złożenie zamówienia z koszyka (prosty wariant) */
    public function submitOrder(Request $request)
    {
        $cartItems = session('cart', []);
        if (empty($cartItems)) {
            return back()->with('error', 'Koszyk jest pusty.');
        }

        DB::beginTransaction();

        try {
            $user = auth()->user();
            $email = $user ? (string)$user->email : '';

            $insert = [
                'numer_zamowienia' => 'ZAM-' . date('Ymd') . '-' . Str::upper(Str::random(6)),
                'id_klienta'       => null,
                'data_wystawienia' => now(),
                'status'           => 'robocze',
                'suma_netto'       => 0,
                'suma_vat'         => 0,
                'suma_brutto'      => 0,
                'uwagi'            => $email ? ('Zamówienie sklepu (' . $email . ')') : 'Zamówienie sklepu',
                'data_utworzenia'  => now(),
            ];

            // jeżeli masz kolumnę id_uzytkownika w zamowienia — zapisz ją
            if ($user && Schema::hasColumn('zamowienia', 'id_uzytkownika')) {
                $insert['id_uzytkownika'] = $user->id_uzytkownika;
            }

            $idZam = DB::table('zamowienia')->insertGetId($insert);

            $sumNetto = 0; $sumVat = 0; $sumBrutto = 0;

            foreach ($cartItems as $item) {
                $prod = DB::table('produkty')
                    ->where('id_produktu', $item['id_produktu'])
                    ->lockForUpdate()
                    ->first();

                if (!$prod) throw new \Exception('Produkt nie istnieje');

                $ilosc     = (float)($item['ilosc'] ?? 0);
                $cenaBrutto = (float)($item['cena_brutto'] ?? 0);
                $vat       = (float)($prod->stawka_vat ?? 0);

                if ($ilosc <= 0) throw new \Exception("Nieprawidłowa ilość: {$prod->nazwa}");
                if ($prod->ilosc < $ilosc) throw new \Exception("Brak ilości: {$prod->nazwa}");

                // Liczymy netto z brutto (bo w sesji trzymasz brutto)
                $cenaNetto = round($cenaBrutto / (1 + $vat / 100), 2);

                $wartBrutto = round($cenaBrutto * $ilosc, 2);
                $wartNetto  = round($cenaNetto * $ilosc, 2);
                $wartVat    = round($wartBrutto - $wartNetto, 2);

                DB::table('zamowienia_pozycje')->insert([
                    'id_zamowienia' => $idZam,
                    'id_produktu'   => $prod->id_produktu,
                    'kod_sku'       => $prod->kod_sku,
                    'nazwa'         => $prod->nazwa,
                    'stawka_vat'    => $vat,
                    'cena_netto'    => $cenaNetto,
                    'ilosc'         => $ilosc,
                    'wart_netto'    => $wartNetto,
                    'wart_vat'      => $wartVat,
                    'wart_brutto'   => $wartBrutto,
                ]);

                DB::table('produkty')
                    ->where('id_produktu', $prod->id_produktu)
                    ->update([
                        'ilosc' => DB::raw('GREATEST(ilosc - ' . $ilosc . ', 0)')
                    ]);

                $sumNetto  += $wartNetto;
                $sumVat    += $wartVat;
                $sumBrutto += $wartBrutto;
            }

            DB::table('zamowienia')
                ->where('id_zamowienia', $idZam)
                ->update([
                    'suma_netto'  => $sumNetto,
                    'suma_vat'    => $sumVat,
                    'suma_brutto' => $sumBrutto,
                ]);

            DB::commit();

            session()->forget('cart');

            // Tu kierujemy do podglądu zamówienia na koncie (żeby od razu je widział)
            return redirect()->route('konto.zamowienie', $idZam)
                ->with('success', 'Zamówienie zostało złożone.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Błąd zamówienia: ' . $e->getMessage());
        }
    }

    public function contactForm()
    {
        return view('pages.kontakt');
    }

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

    public function cart()
    {
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

    public function addToCart(Request $request)
    {
        $request->validate([
            'produkt_id' => 'required|integer|exists:produkty,id_produktu',
            'ilosc'      => 'required|integer|min:1',
        ]);

        $ilosc = (int) $request->input('ilosc', 1);

        $produkt = DB::table('produkty')
            ->select(
                'id_produktu',
                'nazwa',
                'kod_sku',
                'cena_netto',
                'stawka_vat',
                'ilosc',
                DB::raw('ROUND(COALESCE(cena_netto,0) * (1 + COALESCE(stawka_vat,0) / 100), 2) as cena_brutto')
            )
            ->where('id_produktu', $request->produkt_id)
            ->first();

        if (!$produkt) {
            return back()->with('error', 'Nie znaleziono produktu.');
        }

        $ilosc = max(1, $ilosc);
        $stock = (int) ($produkt->ilosc ?? 0);

        // Produkt niedostępny
        if ($stock <= 0) {
            return back()->with('error', 'Produkt jest chwilowo niedostępny.');
        }

        $cart = session()->get('cart', []);

        // Ile już jest w koszyku
        $currentQty = isset($cart[$produkt->id_produktu])
            ? (int)($cart[$produkt->id_produktu]['ilosc'] ?? 0)
            : 0;

        // Ile będzie po dodaniu
        $newQty = $currentQty + $ilosc;

        // ✅ BLOKADA: nie można przekroczyć stanu magazynowego
        if ($newQty > $stock) {
            return back()->with('error', 'Ilość niedostępna. Na magazynie jest tylko ' . $stock . ' szt.');
        }

        // Dodaj/aktualizuj koszyk
        if (isset($cart[$produkt->id_produktu])) {
            $cart[$produkt->id_produktu]['ilosc'] = $newQty;
        } else {
            $cart[$produkt->id_produktu] = [
                'id_produktu' => $produkt->id_produktu,
                'nazwa'       => $produkt->nazwa,
                'kod_sku'     => $produkt->kod_sku,
                'ilosc'       => $ilosc,
                'cena_brutto' => (float)$produkt->cena_brutto,
            ];
        }

        session()->put('cart', $cart);

        return back()->with('success', 'Produkt dodany do koszyka.');
    }


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
            ->sum(fn($p) => ($p['ilosc'] ?? 0) * ($p['cena_brutto'] ?? 0));

        return view('frontend.platnosc-blik', [
            'suma' => $suma
        ]);
    }

    public function blikPay(Request $request)
    {
        $rules = [
            'imie_nazwisko'  => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255'],
            'ulica'          => ['required', 'string', 'max:255'],
            'miasto'         => ['required', 'string', 'max:255'],
            'kod_pocztowy'   => ['required', 'regex:/^[0-9]{2}-[0-9]{3}$/'],
            'dostawa'        => ['required', 'in:kurier,odbior'],
            'platnosc'       => ['required', 'in:blik,przelew,pobranie,karta'],
            'distance'       => ['required', 'integer', 'min:1', 'max:500'],
        ];

        if ($request->platnosc === 'blik') {
            $rules['blik'] = ['required', 'regex:/^[0-9]{6}$/'];
        }

        if ($request->platnosc === 'karta') {
            $rules['card_number'] = ['required', 'regex:/^[0-9]{4} [0-9]{4} [0-9]{4} [0-9]{4}$/'];
            $rules['card_exp']    = ['required', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'];
            $rules['card_cvv']    = ['required', 'digits:3'];
        }

        $validated = $request->validate($rules);

        $cart = collect(session('cart', []));
        if ($cart->isEmpty()) {
            return redirect()
                ->route('koszyk')
                ->with('error', 'Koszyk jest pusty. Dodaj produkty przed złożeniem zamówienia.');
        }

        $distance      = (int)$validated['distance'];
        $dniDostawy    = $this->obliczDniDostawy($distance);
        $numer         = $this->generujNumerZamowienia();
        $now           = now();

        $domyslnyKlientId = 1;
        $idKlienta = (auth()->check() && isset(auth()->user()->id_klienta))
            ? auth()->user()->id_klienta
            : $domyslnyKlientId;

        // UWAGI + zawsze dopisz mail w nawiasie → konto znajdzie zamówienie nawet bez id_uzytkownika
        $uwagi = "Odbiorca: {$validated['imie_nazwisko']} ({$validated['email']}), "
            . "Adres: {$validated['ulica']}, {$validated['kod_pocztowy']} {$validated['miasto']}. "
            . "Dostawa: {$validated['dostawa']}, płatność: {$validated['platnosc']}, "
            . "odległość: {$distance} km, szacowany czas dostawy: {$dniDostawy} dni.";

        DB::beginTransaction();

        try {
            $user = auth()->user();

            $insert = [
                'numer_zamowienia' => $numer,
                'id_klienta'       => $idKlienta,
                'data_wystawienia' => $now->toDateString(),
                'status'           => $this->statusStartowy($validated['platnosc']),
                'suma_netto'       => 0,
                'suma_vat'         => 0,
                'suma_brutto'      => 0,
                'uwagi'            => $uwagi,
                'data_utworzenia'  => $now,
            ];

            if ($user && Schema::hasColumn('zamowienia', 'id_uzytkownika')) {
                $insert['id_uzytkownika'] = $user->id_uzytkownika;
            }

            // Zamowienie::create zakłada fillable – bezpieczniej tu: insertGetId + potem update modelu
            $idZam = DB::table('zamowienia')->insertGetId($insert);

            $sumNetto  = 0;
            $sumVat    = 0;
            $sumBrutto = 0;

            foreach ($cart as $item) {
                $productId   = (int)$item['id_produktu'];
                $ilosc       = (int)$item['ilosc'];
                $cenaBrutto  = (float)$item['cena_brutto'];

                $prod = DB::table('produkty')
                    ->where('id_produktu', $productId)
                    ->lockForUpdate()
                    ->first();

                if (!$prod) throw new \Exception("Produkt ID {$productId} nie istnieje.");
                if ($ilosc <= 0) throw new \Exception("Nieprawidłowa ilość dla produktu {$prod->nazwa}.");
                if ($prod->ilosc < $ilosc) throw new \Exception("Brak ilości: {$prod->nazwa} (dostępne {$prod->ilosc}, zamówiono {$ilosc}).");

                $vat = (float)$prod->stawka_vat;

                $cenaNetto  = round($cenaBrutto / (1 + $vat / 100), 2);
                $wartBrutto = round($cenaBrutto * $ilosc, 2);
                $wartNetto  = round($cenaNetto  * $ilosc, 2);
                $wartVat    = round($wartBrutto - $wartNetto, 2);

                DB::table('zamowienia_pozycje')->insert([
                    'id_zamowienia' => $idZam,
                    'id_produktu'   => $prod->id_produktu,
                    'kod_sku'       => $prod->kod_sku,
                    'nazwa'         => $prod->nazwa,
                    'stawka_vat'    => $vat,
                    'cena_netto'    => $cenaNetto,
                    'ilosc'         => $ilosc,
                    'wart_netto'    => $wartNetto,
                    'wart_vat'      => $wartVat,
                    'wart_brutto'   => $wartBrutto,
                ]);

                DB::table('produkty')
                    ->where('id_produktu', $prod->id_produktu)
                    ->update([
                        'ilosc' => DB::raw('GREATEST(ilosc - ' . $ilosc . ', 0)')
                    ]);

                $sumNetto  += $wartNetto;
                $sumVat    += $wartVat;
                $sumBrutto += $wartBrutto;
            }

            DB::table('zamowienia')->where('id_zamowienia', $idZam)->update([
                'suma_netto'  => $sumNetto,
                'suma_vat'    => $sumVat,
                'suma_brutto' => $sumBrutto,
            ]);

            DB::commit();
            session()->forget('cart');

            // od razu przenieś do konta → klient widzi swoje zamówienie
            return redirect()
                ->route('konto.zamowienie', $idZam)
                ->with('success', 'Zamówienie zostało złożone. Numer zamówienia: ' . $numer);

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Błąd zamówienia: ' . $e->getMessage());
        }
    }

    private function obliczDniDostawy(int $distance): int
    {
        if ($distance <= 50) return 1;
        if ($distance <= 200) return 2;
        return 2 + (int)ceil(($distance - 200) / 200);
    }

    private function generujNumerZamowienia(): string
    {
        $date = now()->format('Ymd');

        do {
            $suffix = Str::upper(Str::random(5));
            $number = "ZAM-{$date}-{$suffix}";
        } while (DB::table('zamowienia')->where('numer_zamowienia', $number)->exists());

        return $number;
    }

    private function statusStartowy(string $platnosc): string
    {
        return match ($platnosc) {
            'przelew' => 'oczekuje na płatność',
            default   => 'zarejestrowane',
        };
    }
}
