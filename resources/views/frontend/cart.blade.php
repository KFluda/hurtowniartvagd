@extends('layouts.app')
@section('title', 'Koszyk')

@section('content')
<div class="container py-4">

    <h1 class="h3 mb-4">
        <i class="bi bi-bag-check"></i> Twój koszyk
    </h1>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($pozycje->isEmpty())
    <div class="bg-white border rounded-3 p-4 text-center text-muted">
        <p class="mb-3">Twój koszyk jest pusty.</p>
        <a href="{{ route('sklep') }}" class="btn btn-primary">
            <i class="bi bi-shop"></i> Przejdź do sklepu
        </a>
    </div>
    @else
    <div class="table-responsive bg-white border rounded-3 shadow-sm">
        <table class="table align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Produkt</th>
                <th>SKU</th>
                <th class="text-end">Cena brutto</th>
                <th class="text-end">Ilość</th>
                <th class="text-end">Wartość</th>
                {{-- tutaj później możesz dodać kolumnę "Akcje" (usuń / zmień ilość) --}}
            </tr>
            </thead>
            <tbody>
            @foreach($pozycje as $item)
            @php
            // Obsługa zarówno tablicy jak i obiektu – na wszelki wypadek
            $id        = is_array($item) ? ($item['id_produktu'] ?? null) : ($item->id_produktu ?? null);
            $nazwa     = is_array($item) ? ($item['nazwa'] ?? '')         : ($item->nazwa ?? '');
            $sku       = is_array($item) ? ($item['kod_sku'] ?? '')       : ($item->kod_sku ?? '');
            $ilosc     = (float)(is_array($item) ? ($item['ilosc'] ?? 0)        : ($item->ilosc ?? 0));
            $cenaBrutto= (float)(is_array($item) ? ($item['cena_brutto'] ?? 0)  : ($item->cena_brutto ?? 0));
            $wartosc   = $ilosc * $cenaBrutto;
            @endphp
            <tr>
                <td>
                    <div class="fw-semibold">{{ $nazwa }}</div>
                    <div class="small text-muted">ID: {{ $id }}</div>
                </td>
                <td class="text-monospace">{{ $sku }}</td>
                <td class="text-end">{{ number_format($cenaBrutto, 2, ',', ' ') }} zł</td>
                <td class="text-end">{{ number_format($ilosc, 2, ',', ' ') }}</td>
                <td class="text-end fw-semibold">{{ number_format($wartosc, 2, ',', ' ') }} zł</td>
            </tr>
            @endforeach
            </tbody>
            <tfoot class="table-light">
            <tr>
                <th colspan="4" class="text-end">Razem do zapłaty:</th>
                <th class="text-end h5 mb-0 text-primary">
                    {{ number_format($sumaBrutto, 2, ',', ' ') }} zł
                </th>
            </tr>
            </tfoot>
        </table>
    </div>

    <div class="mt-3 d-flex justify-content-between">
        <a href="{{ route('sklep') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kontynuuj zakupy
        </a>

        {{-- Tu kiedyś można zrobić przejście do formularza zamówienia / checkout --}}
        <button class="btn btn-success" type="button" disabled>
            <i class="bi bi-credit-card"></i> Finalizacja zamówienia (w przygotowaniu)
        </button>
    </div>
    @endif

</div>
@endsection
