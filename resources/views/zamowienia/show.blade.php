@extends('layouts.app')
@section('title','Zamówienie #'.$zam->id_zamowienia)

@section('content')
<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">Zamówienie #{{ $zam->id_zamowienia }}</h1>
        <a href="{{ route('zamowienia.index') }}" class="btn btn-outline-secondary btn-sm">Powrót</a>
    </div>

    @if(session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif

    <div class="row g-3">
        <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 h-100">
                <h6 class="mb-3">Klient</h6>
                <div><strong>{{ $zam->klient }}</strong></div>
                <div>NIP: {{ $zam->nip ?? '—' }}</div>
                <div>{{ $zam->ulica ?? '' }}</div>
                <div>{{ $zam->kod_pocztowy ?? '' }} {{ $zam->miasto ?? '' }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 h-100">
                <h6 class="mb-3">Dane dokumentu</h6>
                <div>Data wystawienia: {{ $zam->data_wystawienia }}</div>
                <div>Status: <span class="badge bg-secondary">{{ $zam->status }}</span></div>
                <div class="mt-2">Uwagi: {{ $zam->uwagi ?? '—' }}</div>
            </div>
        </div>
    </div>

    <div class="table-responsive bg-white border rounded-3 mt-3">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>SKU</th>
                <th>Nazwa</th>
                <th class="text-end">Ilość</th>
                <th class="text-end">Cena netto</th>
                <th class="text-end">Stawka VAT</th>
                <th class="text-end">Netto</th>
                <th class="text-end">VAT</th>
                <th class="text-end">Brutto</th>
            </tr>
            </thead>
            <tbody>
            @foreach($pozycje as $i => $p)
            <tr>
                <td>{{ $i+1 }}</td>
                <td class="text-monospace">{{ $p->kod_sku }}</td>
                <td>{{ $p->nazwa }}</td>
                <td class="text-end">{{ number_format($p->ilosc,3,',',' ') }}</td>
                <td class="text-end">{{ number_format($p->cena_netto,2,',',' ') }} zł</td>
                <td class="text-end">{{ number_format($p->stawka_vat,2) }}%</td>
                <td class="text-end">{{ number_format($p->wart_netto,2,',',' ') }} zł</td>
                <td class="text-end">{{ number_format($p->wart_vat,2,',',' ') }} zł</td>
                <td class="text-end">{{ number_format($p->wart_brutto,2,',',' ') }} zł</td>
            </tr>
            @endforeach
            </tbody>
            <tfoot class="table-light">
            <tr>
                <th colspan="6" class="text-end">Razem:</th>
                <th class="text-end">{{ number_format($zam->suma_netto,2,',',' ') }} zł</th>
                <th class="text-end">{{ number_format($zam->suma_vat,2,',',' ') }} zł</th>
                <th class="text-end">{{ number_format($zam->suma_brutto,2,',',' ') }} zł</th>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
