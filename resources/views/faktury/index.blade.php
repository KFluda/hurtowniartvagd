@extends('layouts.app')
@section('title','Faktury sprzedaży')

@section('content')
<div class="container py-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 mb-0">Faktury sprzedaży</h1>
        <form method="get" class="d-flex gap-2">
            <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Szukaj: nr zamówienia / klient" style="min-width: 280px">
            <button class="btn btn-primary">Szukaj</button>
        </form>
    </div>

    <div class="table-responsive bg-white border rounded-3">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Nr zamówienia</th>
                <th>Klient</th>
                <th>Data</th>
                <th class="text-end">Netto</th>
                <th class="text-end">VAT</th>
                <th class="text-end">Brutto</th>
                <th style="width:140px">Akcje</th>
            </tr>
            </thead>
            <tbody>
            @forelse($zamowienia as $z)
            <tr>
                <td class="text-monospace">{{ $z->numer_zamowienia }}</td>
                <td>{{ $z->klient ?? '—' }}</td>
                <td>{{ \Carbon\Carbon::parse($z->data_wystawienia)->format('d.m.Y') }}</td>
                <td class="text-end">{{ number_format($z->suma_netto, 2, ',', ' ') }} zł</td>
                <td class="text-end">{{ number_format($z->suma_vat, 2, ',', ' ') }} zł</td>
                <td class="text-end fw-bold">{{ number_format($z->suma_brutto, 2, ',', ' ') }} zł</td>
                <td>
                    <a class="btn btn-sm btn-outline-primary"
                       href="{{ route('faktury.pdf',$z->id_zamowienia) }}">
                        Pobierz PDF
                    </a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Brak zamówień do zafakturowania.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $zamowienia->links() }}
    </div>
</div>
@endsection
