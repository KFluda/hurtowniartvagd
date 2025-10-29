<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ZamowienieController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /* ===================== LISTA (jeśli używasz) ===================== */
    public function index(Request $request)
    {
        $zamowienia = DB::table('zamowienia as z')
            ->leftJoin('klienci as k', 'k.id_klienta', '=', 'z.id_klienta')
            ->select('z.*', 'k.nazwa as klient')
            ->orderByDesc('z.id_zamowienia')
            ->paginate(20);

        return view('zamowienia.index', compact('zamowienia'));
    }

    /* ============== FORMULARZ NOWEGO ZAMÓWIENIA ============== */
    public function create(Request $request)
    {
        $klienci = DB::table('klienci')->orderBy('nazwa')->get();

        // prosta wyszukiwarka produktów (bez JS; przez GET ?q=)
        $q    = trim((string) $request->query('q', ''));
        $rows = max((int) $request->query('rows', 5), 1);

        $produkty = DB::table('produkty')
            ->when($q, function ($qr) use ($q) {
                $qr->where(function ($w) use ($q) {
                    $w->where('nazwa', 'like', "%$q%")
                        ->orWhere('kod_sku', 'like', "%$q%")
                        ->orWhere('ean', 'like', "%$q%");
                });
            })
            ->orderBy('nazwa')
            ->limit(500) // limit bezpieczeństwa
            ->get();

        // proponowany unikalny numer (readonly w formularzu)
        $proponowanyNumer = $this->generateUniqueOrderNumber();

        return view('zamowienia.create', compact('klienci', 'produkty', 'rows', 'q', 'proponowanyNumer'));
    }

    /* ======================= ZAPIS ======================= */
    public function store(Request $request)
    {
        $request->validate([
            'numer_zamowienia'        => ['required', 'string', 'max:40', 'unique:zamowienia,numer_zamowienia'],
            'id_klienta'              => ['required', 'integer', 'exists:klienci,id_klienta'],
            'data_wystawienia'        => ['required', 'date'],
            'uwagi'                   => ['nullable', 'string'],

            'pozycje'                 => ['array'],
            'pozycje.*.id_produktu'   => ['nullable', 'integer', 'exists:produkty,id_produktu'],
            'pozycje.*.ilosc'         => ['nullable', 'numeric', 'min:0.001'],
            'pozycje.*.cena_netto'    => ['nullable', 'numeric', 'min:0'],
        ]);

        // odfiltruj puste wiersze
        $pozycje = $request->input('pozycje', []);
        $pozycje = array_values(array_filter($pozycje, function ($p) {
            return !empty($p['id_produktu']) && !empty($p['ilosc']) && (float)$p['ilosc'] > 0;
        }));

        if (empty($pozycje)) {
            return back()->withInput()->with('error', 'Dodaj przynajmniej jedną pozycję.');
        }

        DB::beginTransaction();
        try {
            // nagłówek zamówienia
            $idZam = DB::table('zamowienia')->insertGetId([
                'numer_zamowienia'  => $request->numer_zamowienia ?: $this->generateUniqueOrderNumber(),
                'id_klienta'        => (int) $request->id_klienta,
                'data_wystawienia'  => $request->data_wystawienia,
                'status'            => 'robocze',
                'suma_netto'        => 0,
                'suma_vat'          => 0,
                'suma_brutto'       => 0,
                'uwagi'             => $request->uwagi,
                'data_utworzenia'   => now(),
            ]);

            /* ------- FAZA 1: SPRAWDZENIE DOSTĘPNOŚCI (z blokadą) ------- */
            $braki = [];

            foreach ($pozycje as $poz) {
                $prod = DB::table('produkty')
                    ->where('id_produktu', (int)$poz['id_produktu'])
                    ->lockForUpdate() // blokujemy do końca transakcji
                    ->first();

                if (!$prod) {
                    $braki[] = 'Produkt ID ' . (int)$poz['id_produktu'] . ' nie istnieje.';
                    continue;
                }

                $iloscZam = (float)$poz['ilosc'];

                if ($iloscZam <= 0) {
                    $braki[] = "Nieprawidłowa ilość dla produktu {$prod->nazwa}.";
                    continue;
                }

                if ($prod->ilosc < $iloscZam) {
                    $braki[] = "Brak wystarczającej ilości: {$prod->nazwa} (dostępne {$prod->ilosc}, zamówiono {$iloscZam}).";
                }
            }

            if (!empty($braki)) {
                DB::rollBack();
                return back()
                    ->withInput()
                    ->with('error', "Nie można utworzyć zamówienia:\n- " . implode("\n- ", $braki));
            }

            /* ------- FAZA 2: ZAPIS POZYCJI + DEKREMENT STANÓW ------- */
            $sumNetto = 0; $sumVat = 0; $sumBrutto = 0;

            foreach ($pozycje as $poz) {
                $prod = DB::table('produkty')
                    ->where('id_produktu', (int)$poz['id_produktu'])
                    ->lockForUpdate()
                    ->first();

                $ilosc     = (float) $poz['ilosc'];
                $cenaNetto = (isset($poz['cena_netto']) && $poz['cena_netto'] !== '') ? (float)$poz['cena_netto'] : 0.00;
                $vat       = (float) $prod->stawka_vat;

                $wartNetto  = round($ilosc * $cenaNetto, 2);
                $wartVat    = round($wartNetto * ($vat / 100), 2);
                $wartBrutto = round($wartNetto + $wartVat, 2);

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

                // bezpieczny dekrement (nie spadnie poniżej 0)
                DB::table('produkty')
                    ->where('id_produktu', $prod->id_produktu)
                    ->update([
                        'ilosc' => DB::raw('GREATEST(ilosc - ' . $ilosc . ', 0)')
                    ]);

                $sumNetto  += $wartNetto;
                $sumVat    += $wartVat;
                $sumBrutto += $wartBrutto;
            }

            // podsumowanie w nagłówku
            DB::table('zamowienia')->where('id_zamowienia', $idZam)->update([
                'suma_netto'  => $sumNetto,
                'suma_vat'    => $sumVat,
                'suma_brutto' => $sumBrutto,
            ]);

            DB::commit();

            return redirect()
                ->route('zamowienia.index') // u Ciebie /zamowienia = create
                ->with('success', '✅ Zamówienie zostało pomyślnie utworzone!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Błąd zapisu: ' . $e->getMessage());
        }
    }

    /* ===================== PODGLĄD ===================== */
    public function show($id)
    {
        $zam = DB::table('zamowienia as z')
            ->leftJoin('klienci as k', 'k.id_klienta', '=', 'z.id_klienta')
            ->select(
                'z.*',
                'k.nazwa as klient',
                'k.nip',
                'k.miasto',
                'k.ulica',
                'k.kod_pocztowy'
            )
            ->where('z.id_zamowienia', $id)
            ->first();

        if (!$zam) {
            abort(404);
        }

        $pozycje = DB::table('zamowienia_pozycje')
            ->where('id_zamowienia', $id)
            ->orderBy('id_pozycji')
            ->get();

        return view('zamowienia.show', compact('zam', 'pozycje'));
    }

    /* ============== GENERATOR NUMERU (unikalny) ============== */
    private function generateUniqueOrderNumber(): string
    {
        do {
            $candidate = 'ZAM-' . date('Ymd') . '-' . Str::upper(Str::random(6));
            $exists = DB::table('zamowienia')
                ->where('numer_zamowienia', $candidate)
                ->exists();
        } while ($exists);

        return $candidate;
    }
}
