@extends('layouts.app')
@section('title','Faktura '.$faktura->numer)

@section('content')
<div class="container py-3">
    @if(session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif

    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="h4 mb-1">Faktura VAT: {{ $faktura->numer }}</h1>
            <div class="text-muted">Data wystawienia: {{ \Carbon\Carbon::parse($faktura->data_wystawienia)->format('Y-m-d') }}</div>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('faktury.create') }}">Wystaw kolejną</a>
    </div>

    <div class="row g-3 my-3">
        <div class="col-md-6">
            <div class="border rounded-3 p-3 bg-white">
                <h6>Sprzedawca</h6>
                <div>{{ $sprzedawca['nazwa'] }}</div>
                <div>NIP: {{ $sprzedawca['nip'] }}</div>
                <div>{{ $sprzedawca['ulica'] }}</div>
                <div>{{ $sprzedawca['kod'] }} {{ $sprzedawca['miasto'] }}</div>
                <div>{{ $sprzedawca['kraj'] }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="border rounded-3 p-3 bg-white">
                <h6>Nabywca</h6>
                <div>{{ $faktura->nazwa }}</div>
                <div>NIP: {{ $faktura->nip }}</div>
                <div>{{ $faktura->ulica }}</div>
                <div>{{ $faktura->kod_pocztowy }} {{ $faktura->miasto }}</div>
                <div>{{ $faktura->kraj }}</div>
            </div>
        </div>
    </div>

    <div class="table-responsive bg-white border rounded-3">
        <table class="table table-sm mb-0">
            <thead>
            <tr>
                <th>Lp</th><th>Nazwa</th>
                <th class="text-end">Ilość</th>
                <th class="text-end">Cena netto</th>
                <th class="text-end">Wartość netto</th>
                <th class="text-end">VAT %</th>
                <th class="text-end">Kwota VAT</th>
                <th class="text-end">Brutto</th>
            </tr>
            </thead>
            <tbody>
            @foreach($pozycje as $p)
            <tr>
                <td>{{ $p->lp }}</td>
                <td>{{ $p->nazwa_produktu }}</td>
                <td class="text-end">{{ number_format($p->ilosc,2,',',' ') }}</td>
                <td class="text-end">{{ number_format($p->cena_netto,2,',',' ') }} zł</td>
                <td class="text-end">{{ number_format($p->wart_netto,2,',',' ') }} zł</td>
                <td class="text-end">{{ rtrim(rtrim(number_format($p->stawka_vat,2,',',' '),'0'),',') }}%</td>
                <td class="text-end">{{ number_format($p->kwota_vat,2,',',' ') }} zł</td>
                <td class="text-end">{{ number_format($p->wart_brutto,2,',',' ') }} zł</td>
            </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <th colspan="4" class="text-end">Razem:</th>
                <th class="text-end">{{ number_format($faktura->suma_netto,2,',',' ') }} zł</th>
                <th></th>
                <th class="text-end">{{ number_format($faktura->suma_vat,2,',',' ') }} zł</th>
                <th class="text-end">{{ number_format($faktura->suma_brutto,2,',',' ') }} zł</th>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
