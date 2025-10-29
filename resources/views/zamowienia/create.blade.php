@extends('layouts.app')
@section('title','Nowe zamówienie')

@section('content')
<div class="container py-3">
    @if(session('success'))
    <div class="alert alert-success text-center fw-semibold">
        {{ session('success') }}
    </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Nowe zamówienie</h1>
        <div class="d-flex gap-2">
            <form method="get" action="{{ route('zamowienia.create') }}" class="d-flex gap-2">
                <input type="hidden" name="rows" value="{{ $rows }}">
                <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Szukaj produktu: nazwa / SKU / EAN" style="min-width: 280px">
                <button class="btn btn-outline-secondary">Szukaj</button>
            </form>
            <a href="{{ route('zamowienia.create', ['rows' => $rows + 5, 'q' => $q]) }}" class="btn btn-outline-secondary">+5 wierszy</a>
        </div>
    </div>

    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
    @if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="post" action="{{ route('zamowienia.store') }}" class="bg-white border rounded-3 p-3">
        @csrf

        <div class="row g-3 mb-2">
            <div class="col-md-4">
                <label class="form-label">Numer zamówienia</label>
                <input name="numer_zamowienia" class="form-control" value="{{ old('numer_zamowienia', $proponowanyNumer) }}" readonly>
                <div class="form-text">Generowany automatycznie (unikalny).</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Klient</label>
                <select name="id_klienta" class="form-select" required>
                    <option value="">— wybierz —</option>
                    @foreach($klienci as $k)
                    <option value="{{ $k->id_klienta }}" @selected(old('id_klienta')==$k->id_klienta)>{{ $k->nazwa }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Data wystawienia</label>
                <input type="date" name="data_wystawienia" class="form-control" value="{{ old('data_wystawienia', date('Y-m-d')) }}" required>
            </div>
            <div class="col-md-12">
                <label class="form-label">Uwagi</label>
                <input type="text" name="uwagi" class="form-control" value="{{ old('uwagi') }}">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="table-light">
                <tr>
                    <th style="width:48%">Produkt ({{ $produkty->count() }} wyników {{ $q ? 'dla: "'.$q.'"' : '' }})</th>
                    <th style="width:12%">Ilość</th>
                    <th style="width:18%">Cena netto</th>
                    <th>Info</th>
                </tr>
                </thead>
                <tbody>
                @for($i=0; $i<$rows; $i++)
                <tr>
                    <td>
                        <select name="pozycje[{{ $i }}][id_produktu]" class="form-select">
                            <option value="">— wybierz produkt —</option>
                            @foreach($produkty as $p)
                            <option value="{{ $p->id_produktu }}">
                                {{ $p->nazwa }} ({{ $p->kod_sku }}) @if($p->ean) | EAN: {{ $p->ean }} @endif
                            </option>
                            @endforeach
                        </select>
                    </td>
                    <td><input name="pozycje[{{ $i }}][ilosc]" type="number" step="0.001" min="0" class="form-control" value="{{ old("pozycje.$i.ilosc") }}"></td>
                    <td><input name="pozycje[{{ $i }}][cena_netto]" type="number" step="0.01" min="0" class="form-control" value="{{ old("pozycje.$i.cena_netto") }}"></td>
                    <td class="text-muted small">VAT i wartości przeliczymy po zapisie</td>
                </tr>
                @endfor
                </tbody>
            </table>
        </div>

        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary">Zapisz zamówienie</button>
            <a href="{{ route('zamowienia.index') }}" class="btn btn-outline-secondary">Anuluj</a>
        </div>
    </form>
</div>
@endsection
