<?php

namespace App\Http\Controllers;

use App\Models\StanMagazynowy;

class MagazynController extends Controller
{
    public function stany()
    {
        $stany = StanMagazynowy::orderBy('id_magazynu')->orderBy('id_produktu')->paginate(25);
        return view('magazyn.stany', compact('stany'));
    }
}
