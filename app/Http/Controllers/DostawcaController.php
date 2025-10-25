<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DostawcaController extends Controller
{
    public function __construct() { $this->middleware('auth'); }

    private function table()
    {
        return DB::getSchemaBuilder()->hasTable('Dostawcy') ? 'Dostawcy' : 'dostawcy';
    }

    public function index(Request $request)
    {
        $q = trim((string)$request->get('q',''));
        $table = $this->table();

        $dostawcy = DB::table($table)
            ->when($q, function ($qr) use ($q) {
                return $qr->where(function($x) use ($q){
                    $x->where('nazwa','like',"%$q%")
                        ->orWhere('nip','like',"%$q%")
                        ->orWhere('miasto','like',"%$q%");
                });
            })
            ->orderBy('nazwa')
            ->paginate(20)
            ->withQueryString();

        return view('dostawcy.index', compact('dostawcy','q'));
    }

    public function create()
    {
        $dostawca = (object)[
            'nazwa'=>'','nip'=>'','ulica'=>'','kod_pocztowy'=>'','miasto'=>'','kraj'=>'Polska',
            'email'=>'','telefon'=>'','aktywny'=>1,
        ];
        return view('dostawcy.form', ['mode'=>'create','dostawca'=>$dostawca]);
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
        ],[
            'nazwa.required' => 'Podaj nazwę.',
            'nip.required'   => 'Podaj NIP.',
        ]);

        $data['aktywny'] = isset($data['aktywny']) ? (int)$data['aktywny'] : 1;

        try {
            DB::table($this->table())->insert($data);
            return redirect()->route('dostawcy.index')->with('status','Dostawca dodany.');
        } catch (\Exception $e) {
            return back()->withErrors(['msg'=>'Błąd zapisu: '.$e->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $dostawca = DB::table($this->table())->where('id_dostawcy',$id)->first();
        if (!$dostawca) abort(404);
        return view('dostawcy.form', ['mode'=>'edit','dostawca'=>$dostawca,'id'=>$id]);
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

        try {
            DB::table($this->table())->where('id_dostawcy',$id)->update($data);
            return redirect()->route('dostawcy.index')->with('status','Zapisano zmiany.');
        } catch (\Exception $e) {
            return back()->withErrors(['msg'=>'Błąd zapisu: '.$e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            DB::table($this->table())->where('id_dostawcy',$id)->delete();
            return redirect()->route('dostawcy.index')->with('status','Dostawca usunięty.');
        } catch (\Exception $e) {
            return redirect()->route('dostawcy.index')->withErrors(['msg'=>'Nie można usunąć: '.$e->getMessage()]);
        }
    }
}
