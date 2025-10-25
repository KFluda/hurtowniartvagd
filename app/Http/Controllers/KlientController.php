<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KlientController extends Controller
{
    public function __construct() { $this->middleware('auth'); }

    private function table()
    {
        return DB::getSchemaBuilder()->hasTable('Klienci') ? 'Klienci' : 'klienci';
    }

    public function index(Request $request)
    {
        $q = trim((string)$request->get('q',''));
        $klienci = DB::table($this->table())
            ->when($q, fn($qr)=>$qr->where('nazwa','like',"%$q%")
                ->orWhere('nip','like',"%$q%")
                ->orWhere('miasto','like',"%$q%"))
            ->orderBy('nazwa')
            ->paginate(20)
            ->withQueryString();

        return view('klienci.index', compact('klienci','q'));
    }

    public function create()
    {
        $klient = (object)[
            'nazwa'=>'','nip'=>'','ulica'=>'','kod_pocztowy'=>'','miasto'=>'',
            'kraj'=>'Polska','email'=>'','telefon'=>'','aktywny'=>1,
        ];
        return view('klienci.form', ['mode'=>'create','klient'=>$klient]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nazwa'        => ['required','string','max:255'],
            'nip'          => ['required','string','max:20'],
            'ulica'        => ['nullable','string','max:255'],
            'kod_pocztowy' => ['nullable','string','max:12'],
            'miasto'       => ['nullable','string','max:120'],
            'kraj'         => ['nullable','string','max:80'],
            'email'        => ['nullable','string','max:255'],
            'telefon'      => ['nullable','string','max:40'],
            'aktywny'      => ['nullable','in:0,1'],
        ]);
        $data['aktywny'] = isset($data['aktywny']) ? (int)$data['aktywny'] : 1;

        DB::table($this->table())->insert($data);
        return redirect()->route('klienci.index')->with('status','Klient dodany.');
    }

    public function edit($id)
    {
        $klient = DB::table($this->table())->where('id_klienta',$id)->first();
        if (!$klient) abort(404);
        return view('klienci.form', ['mode'=>'edit','klient'=>$klient,'id'=>$id]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nazwa'        => ['required','string','max:255'],
            'nip'          => ['required','string','max:20'],
            'ulica'        => ['nullable','string','max:255'],
            'kod_pocztowy' => ['nullable','string','max:12'],
            'miasto'       => ['nullable','string','max:120'],
            'kraj'         => ['nullable','string','max:80'],
            'email'        => ['nullable','string','max:255'],
            'telefon'      => ['nullable','string','max:40'],
            'aktywny'      => ['nullable','in:0,1'],
        ]);
        $data['aktywny'] = isset($data['aktywny']) ? (int)$data['aktywny'] : 0;

        DB::table($this->table())->where('id_klienta',$id)->update($data);
        return redirect()->route('klienci.index')->with('status','Zmieniono dane klienta.');
    }

    public function destroy($id)
    {
        DB::table($this->table())->where('id_klienta',$id)->delete();
        return redirect()->route('klienci.index')->with('status','Klient usunięty.');
    }
}
