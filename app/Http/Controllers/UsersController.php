<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']); // ← Twój middleware admin-only
    }

    /** Lista użytkowników z wyszukiwarką */
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q',''));

        $users = DB::table('uzytkownicy')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('email', 'like', "%{$q}%")
                        ->orWhere('imie_nazwisko', 'like', "%{$q}%")
                        ->orWhere('rola', 'like', "%{$q}%");
                });
            })
            ->orderBy('id_uzytkownika')
            ->paginate(20)
            ->withQueryString();

        return view('uzytkownicy.index', compact('users','q'));
    }

    /** Formularz dodawania */
    public function create()
    {
        $roles = $this->roles();
        $user = (object)[
            'email'         => '',
            'imie_nazwisko' => '',
            'rola'          => 'MAGAZYN',
            'aktywny'       => 1,
        ];
        return view('uzytkownicy.form', [
            'mode'  => 'create',
            'user'  => $user,
            'roles' => $roles,
        ]);
    }

    /** Zapis nowego */
    public function store(Request $request)
    {
        $data = $request->validate([
            'email'         => ['required','email','max:255','unique:uzytkownicy,email'],
            'haslo'         => ['required','string','min:8'],
            'imie_nazwisko' => ['required','string','max:255'],
            'rola'          => ['required', Rule::in(array_keys($this->roles()))],
            'aktywny'       => ['required','in:0,1'],
        ]);

        DB::table('uzytkownicy')->insert([
            'email'          => $data['email'],
            'haslo'          => bcrypt($data['haslo']),
            'imie_nazwisko'  => $data['imie_nazwisko'],
            'rola'           => $data['rola'],
            'aktywny'        => (int)$data['aktywny'],
            'data_utworzenia'=> now(),
        ]);

        return redirect()->route('uzytkownicy.index')->with('status','Użytkownik dodany.');
    }

    /** Formularz edycji */
    public function edit($id)
    {
        $user = DB::table('uzytkownicy')->where('id_uzytkownika',$id)->first();
        if (!$user) abort(404);

        return view('uzytkownicy.form', [
            'mode'  => 'edit',
            'id'    => $id,
            'user'  => $user,
            'roles' => $this->roles(),
        ]);
    }

    /** Zapis edycji */
    public function update(Request $request, $id)
    {
        $user = DB::table('uzytkownicy')->where('id_uzytkownika',$id)->first();
        if (!$user) abort(404);

        $data = $request->validate([
            'email'         => ['required','email','max:255', Rule::unique('uzytkownicy','email')->ignore($id,'id_uzytkownika')],
            'haslo'         => ['nullable','string','min:8'],
            'imie_nazwisko' => ['required','string','max:255'],
            'rola'          => ['required', Rule::in(array_keys($this->roles()))],
            'aktywny'       => ['required','in:0,1'],
        ]);

        $update = [
            'email'         => $data['email'],
            'imie_nazwisko' => $data['imie_nazwisko'],
            'rola'          => $data['rola'],
            'aktywny'       => (int)$data['aktywny'],
        ];
        if (!empty($data['haslo'])) {
            $update['haslo'] = bcrypt($data['haslo']);
        }

        DB::table('uzytkownicy')->where('id_uzytkownika',$id)->update($update);

        return redirect()->route('uzytkownicy.index')->with('status','Dane użytkownika zapisane.');
    }

    /** Usunięcie */
    public function destroy($id)
    {
        // dla bezpieczeństwa: nie pozwól usunąć samego siebie
        $me = Auth::user();
        if ($me && (int)$me->id_uzytkownika === (int)$id) {
            return back()->with('error','Nie możesz usunąć własnego konta.');
        }

        DB::table('uzytkownicy')->where('id_uzytkownika',$id)->delete();
        return back()->with('status','Użytkownik usunięty.');
    }

    /** Słownik ról */
    private function roles(): array
    {
        return [
            'ADMIN'     => 'ADMIN',
            'KIEROWNIK' => 'KIEROWNIK',
            'PRACOWNIK' => 'PRACOWNIK',
        ];
    }

}
