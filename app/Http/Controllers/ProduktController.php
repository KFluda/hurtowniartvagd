<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProduktController extends Controller
{
    public function __construct() { $this->middleware('auth'); }

    public function index(Request $request)
    {
        $q = trim((string)$request->get('q',''));

        $produkty = DB::table('produkty as p')
            ->leftJoin('producenci as pr', 'pr.id_producenta', '=', 'p.id_producenta')
            ->leftJoin('kategorie as k', 'k.id_kategorii', '=', 'p.id_kategorii')
            ->select('p.*', 'pr.nazwa as producent', 'k.nazwa as kategoria')
            ->when($q, function($qr) use ($q){
                $qr->where(function($w) use ($q){
                    $w->where('p.nazwa','like',"%$q%")
                        ->orWhere('p.kod_sku','like',"%$q%")
                        ->orWhere('p.ean','like',"%$q%");
                });
            })
            ->orderBy('p.nazwa')
            ->paginate(20)
            ->withQueryString();

        return view('produkty.index', compact('produkty','q'));
    }

    public function create()
    {
        $producenci = DB::table('producenci')->orderBy('nazwa')->get();
        $kategorie  = DB::table('kategorie')->orderBy('nazwa')->get();

        $produkt = (object)[
            'kod_sku' => '', 'ean' => '', 'nazwa' => '',
            'id_producenta' => null, 'id_kategorii' => null,
            'waga_kg' => null, 'glebokosc_mm' => null, 'szerokosc_mm' => null, 'wysokosc_mm' => null,
            'gwarancja_miesiecy' => 24, 'czy_z_numerem_seryjnym' => 0,
            // jeśli kolumna stawka_vat nie jest już używana, możesz to pole całkiem usunąć
            'stawka_vat' => null,
            'aktywny' => 1,
            'ilosc' => 0, // ✅ domyślny stan magazynowy
        ];

        return view('produkty.form', [
            'mode' => 'create', 'produkt' => $produkt,
            'producenci' => $producenci, 'kategorie' => $kategorie
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kod_sku'                => ['required','string','max:64','unique:produkty,kod_sku'],
            'ean'                    => ['nullable','string','max:64'],
            'nazwa'                  => ['required','string','max:255'],
            'id_producenta'          => ['nullable','integer','exists:producenci,id_producenta'],
            'id_kategorii'           => ['nullable','integer','exists:kategorie,id_kategorii'],
            'waga_kg'                => ['nullable','numeric','min:0'],
            'glebokosc_mm'           => ['nullable','integer','min:0'],
            'szerokosc_mm'           => ['nullable','integer','min:0'],
            'wysokosc_mm'            => ['nullable','integer','min:0'],
            'gwarancja_miesiecy'     => ['nullable','integer','min:0'],
            'czy_z_numerem_seryjnym' => ['nullable','in:0,1'],
            // 'stawka_vat'           => ['nullable','numeric','min:0'], // ← jeżeli nie używasz, usuń
            'aktywny'                => ['required','in:0,1'],
            'ilosc'                  => ['required','numeric','min:0'], // ✅ NOWE: zapisujemy stan
        ]);

        DB::table('produkty')->insert($data + ['data_utworzenia' => now()]);
        return redirect()->route('produkty.index')->with('status','Produkt dodany.');
    }

    public function edit($id)
    {
        $produkt = DB::table('produkty')->where('id_produktu',$id)->first();
        abort_unless($produkt, 404);

        $producenci = DB::table('producenci')->orderBy('nazwa')->get();
        $kategorie  = DB::table('kategorie')->orderBy('nazwa')->get();

        return view('produkty.form', [
            'mode' => 'edit','id' => $id,'produkt' => $produkt,
            'producenci' => $producenci,'kategorie' => $kategorie
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nazwa'                => 'required|string|max:255',
            'kod_sku'              => ['required','string','max:64', Rule::unique('produkty','kod_sku')->ignore($id,'id_produktu')],
            'ean'                  => 'nullable|string|max:64',
            'id_producenta'        => 'nullable|integer|exists:producenci,id_producenta',
            'id_kategorii'         => 'nullable|integer|exists:kategorie,id_kategorii',
            'waga_kg'              => 'nullable|numeric|min:0',
            'glebokosc_mm'         => 'nullable|integer|min:0',
            'szerokosc_mm'         => 'nullable|integer|min:0',
            'wysokosc_mm'          => 'nullable|integer|min:0',
            'gwarancja_miesiecy'   => 'nullable|integer|min:0',
            'czy_z_numerem_seryjnym'=> 'nullable|in:0,1',
            // 'stawka_vat'         => 'nullable|numeric|min:0', // ← jeżeli nie używasz, usuń
            'aktywny'              => 'nullable|in:0,1',
            'ilosc'                => 'required|numeric|min:0', // ✅ może być też decimal
        ]);

        DB::table('produkty')->where('id_produktu', $id)->update([
            'nazwa'                 => $request->nazwa,
            'kod_sku'               => $request->kod_sku,
            'ean'                   => $request->ean,
            'id_producenta'         => $request->id_producenta,
            'id_kategorii'          => $request->id_kategorii,
            'waga_kg'               => $request->waga_kg,
            'glebokosc_mm'          => $request->glebokosc_mm,
            'szerokosc_mm'          => $request->szerokosc_mm,
            'wysokosc_mm'           => $request->wysokosc_mm,
            'gwarancja_miesiecy'    => $request->gwarancja_miesiecy,
            'czy_z_numerem_seryjnym'=> $request->czy_z_numerem_seryjnym,
            // 'stawka_vat'          => $request->stawka_vat, // ← jeżeli nie używasz, usuń
            'aktywny'               => $request->boolean('aktywny'),
            'ilosc'                 => $request->ilosc,          // ✅ zapis ilości
        ]);

        return redirect()->route('produkty.index')->with('success', 'Produkt zaktualizowany.');
    }

    public function destroy($id)
    {
        try {
            DB::table('produkty')->where('id_produktu',$id)->delete();
            return back()->with('status','Produkt usunięty.');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error','Nie można usunąć: produkt jest użyty w dokumentach.');
        }
    }
}
