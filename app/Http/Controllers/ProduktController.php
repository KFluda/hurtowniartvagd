<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produkt;

class ProduktController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');
        $produkty = Produkt::when($q, function ($qry) use ($q) {
            return $qry->where('nazwa','like',"%{$q}%")
                ->orWhere('kod_sku','like',"%{$q}%")
                ->orWhere('ean','like',"%{$q}%");
        })
            ->orderBy('nazwa')
            ->paginate(20)
            ->withQueryString();

        return view('produkty.index', compact('produkty','q'));
    }
}
