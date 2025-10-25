<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FakturaController extends Controller
{
    public function __construct() { $this->middleware('auth'); }

    public function create()
    {
        $kontrahenci = DB::table('kontrahenci')->orderBy('nazwa')->select('nip','nazwa')->get();
        $pozycjeCount = 5;
        return view('faktury.create', compact('pozycjeCount','kontrahenci'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'numer' => ['required','string','max:50'],
            'data_wystawienia' => ['required','date'],
            'selected_nip' => ['nullable','string','max:20'],
            'nip' => ['nullable','string','max:20'],
            'nazwa' => ['nullable','string','max:255'],
            'ulica' => ['nullable','string','max:255'],
            'kod_pocztowy' => ['nullable','string','max:12'],
            'miasto' => ['nullable','string','max:120'],
            'kraj' => ['nullable','string','max:80'],
            'nazwa_produktu.*' => ['nullable','string','max:255'],
            'ilosc.*' => ['nullable','numeric','min:0'],
            'cena_netto.*' => ['nullable','numeric','min:0'],
            'stawka_vat.*' => ['nullable','numeric','min:0'],
        ]);

        $nip = $data['selected_nip'] ? $data['selected_nip'] : $data['nip'];
        if (!$nip) return back()->withErrors(['nip'=>'Wybierz kontrahenta lub wpisz NIP.'])->withInput();

        $kontr = DB::table('kontrahenci')->where('nip',$nip)->first();
        if (!$kontr) {
            if (empty($data['nazwa'])) return back()->withErrors(['nazwa'=>'Podaj nazwę nabywcy.'])->withInput();
            $idKontr = DB::table('kontrahenci')->insertGetId([
                'nip'=>$nip,'nazwa'=>$data['nazwa'],'ulica'=>$data['ulica'],
                'kod_pocztowy'=>$data['kod_pocztowy'],'miasto'=>$data['miasto'],
                'kraj'=>$data['kraj'] ? $data['kraj'] : 'Polska',
            ]);
        } else {
            $idKontr = $kontr->id_kontrahenta;
        }

        $sumaN=0; $sumaV=0; $sumaB=0;
        DB::beginTransaction();
        try {
            $idF = DB::table('faktury_sprzedazy')->insertGetId([
                'numer'=>$data['numer'],'data_wystawienia'=>$data['data_wystawienia'],
                'id_kontrahenta'=>$idKontr,'suma_netto'=>0,'suma_vat'=>0,'suma_brutto'=>0,
            ]);

            $nazwy=(array)$request->input('nazwa_produktu',[]);
            $ilosci=(array)$request->input('ilosc',[]);
            $ceny=(array)$request->input('cena_netto',[]);
            $stawki=(array)$request->input('stawka_vat',[]);
            $lp=1;

            for($i=0;$i<count($nazwy);$i++){
                $n=trim((string)($nazwy[$i]??'')); $q=(float)($ilosci[$i]??0);
                $c=(float)($ceny[$i]??0); $v=(float)($stawki[$i]??23.00);
                if($n===''||$q<=0) continue;

                $wn=round($q*$c,2); $kv=round($wn*($v/100),2); $wb=round($wn+$kv,2);
                $sumaN+=$wn; $sumaV+=$kv; $sumaB+=$wb;

                DB::table('faktury_pozycje')->insert([
                    'id_faktury'=>$idF,'lp'=>$lp++,'nazwa_produktu'=>$n,
                    'ilosc'=>$q,'cena_netto'=>$c,'stawka_vat'=>$v,
                    'wart_netto'=>$wn,'kwota_vat'=>$kv,'wart_brutto'=>$wb
                ]);
            }
            if($lp===1){ DB::rollBack(); return back()->withErrors(['msg'=>'Dodaj co najmniej jedną pozycję.'])->withInput(); }

            DB::table('faktury_sprzedazy')->where('id_faktury',$idF)->update([
                'suma_netto'=>$sumaN,'suma_vat'=>$sumaV,'suma_brutto'=>$sumaB
            ]);

            DB::commit();
            return redirect()->route('faktury.show',$idF)->with('status','Faktura zapisana.');
        } catch(\Exception $e){
            DB::rollBack();
            return back()->withErrors(['msg'=>'Błąd: '.$e->getMessage()])->withInput();
        }
    }

    public function show($id)
    {
        $f = DB::table('faktury_sprzedazy as fs')
            ->join('kontrahenci as k','k.id_kontrahenta','=','fs.id_kontrahenta')
            ->select('fs.*','k.nip','k.nazwa','k.ulica','k.kod_pocztowy','k.miasto','k.kraj')
            ->where('fs.id_faktury',$id)->first();
        if(!$f) abort(404);

        $poz = DB::table('faktury_pozycje')->where('id_faktury',$id)->orderBy('lp')->get();

        $sprzedawca = [
            'nazwa'=>'Hurtownia RTV/AGD Sp. z o.o.','nip'=>'123-45-67-890',
            'ulica'=>'ul. Magazynowa 1','kod'=>'00-000','miasto'=>'Miasto','kraj'=>'Polska'
        ];

        return view('faktury.show',['faktura'=>$f,'pozycje'=>$poz,'sprzedawca'=>$sprzedawca]);
    }
}
