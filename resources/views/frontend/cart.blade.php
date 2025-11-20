@extends('layouts.app')
@section('title', 'Koszyk')

@section('content')
<div class="container py-4">
    <h1 class="h3 mb-4">Koszyk</h1>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($pozycje->isEmpty())
    <p class="text-muted">Twój koszyk jest pusty.</p>
    @else
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
            <tr>
                <th>Produkt</th>
                <th>Kod SKU</th>
                <th class="text-center" style="width:120px;">Ilość</th>
                <th class="text-end">Cena brutto</th>
                <th class="text-end">Razem</th>
                <th class="text-center" style="width:80px;">Akcje</th>
            </tr>
            </thead>
            <tbody>
            @foreach($pozycje as $item)
            @php
            $ilosc = (int)($item['ilosc'] ?? 0);
            $cena  = (float)($item['cena_brutto'] ?? 0);
            $razem = $ilosc * $cena;
            @endphp
            <tr>
                <td>{{ $item['nazwa'] }}</td>
                <td>{{ $item['kod_sku'] }}</td>

                {{-- Zmiana ilości --}}
                <td class="text-center">
                    <form action="{{ route('koszyk.update') }}" method="POST" class="d-inline-flex">
                        @csrf
                        <input type="hidden" name="produkt_id" value="{{ $item['id_produktu'] }}">
                        <input type="number" name="ilosc" min="1"
                               class="form-control form-control-sm text-center"
                               style="width:70px"
                               value="{{ $ilosc }}">
                        <button class="btn btn-sm btn-outline-secondary ms-1">
                            <i class="bi bi-check"></i>
                        </button>
                    </form>
                </td>

                <td class="text-end">
                    {{ number_format($cena, 2, ',', ' ') }} zł
                </td>
                <td class="text-end">
                    {{ number_format($razem, 2, ',', ' ') }} zł
                </td>

                {{-- Usuń pozycję --}}
                <td class="text-center">
                    <form action="{{ route('koszyk.remove') }}" method="POST">
                        @csrf
                        <input type="hidden" name="produkt_id" value="{{ $item['id_produktu'] }}">
                        <button class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <th colspan="4" class="text-end">Suma brutto:</th>
                <th class="text-end">
                    {{ number_format($sumaBrutto, 2, ',', ' ') }} zł
                </th>
                <th></th>
            </tr>
            </tfoot>
        </table>
    </div>
    <div class="text-end mt-3">
        <a href="{{ route('platnosc.blik') }}" class="btn btn-primary btn-lg">
            Przejdź do płatności
        </a>
    </div>

    @endif
</div>
@endsection
