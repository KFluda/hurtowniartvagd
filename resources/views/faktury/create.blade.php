@extends('layouts.app')
@section('title','Nowa faktura sprzedaży')

@section('content')
<div class="container py-3">
    <h1 class="h4 mb-3">Nowa faktura sprzedaży</h1>

    @if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="post" action="{{ route('faktury.store') }}" class="bg-white border rounded-3 p-3">
        @csrf
        <div class="row g-3">
            <div class="col-md-3"><label class="form-label">Numer</label>
                <input name="numer" class="form-control" value="{{ old('numer','FS/'.date('Y').'/001') }}" required>
            </div>
            <div class="col-md-3"><label class="form-label">Data wystawienia</label>
                <input type="date" name="data_wystawienia" class="form-control" value="{{ old('data_wystawienia',date('Y-m-d')) }}" required>
            </div>
        </div>

        <hr><h6>Kontrahent</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Wybierz z listy (opcjonalnie)</label>
                <select name="selected_nip" class="form-select">
                    <option value="">— wybierz —</option>
                    @foreach($kontrahenci as $k)
                    <option value="{{ $k->nip }}" @selected(old('selected_nip')===$k->nip)>{{ $k->nazwa }} ({{ $k->nip }})</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="text-muted small mt-2">Lub wpisz ręcznie dane:</div>
        <div class="row g-3 mt-1">
            <div class="col-md-3"><label class="form-label">NIP</label><input name="nip" class="form-control" value="{{ old('nip') }}"></div>
            <div class="col-md-6"><label class="form-label">Nazwa / Imię i nazwisko</label><input name="nazwa" class="form-control" value="{{ old('nazwa') }}"></div>
            <div class="col-md-6"><label class="form-label">Ulica i nr</label><input name="ulica" class="form-control" value="{{ old('ulica') }}"></div>
            <div class="col-md-3"><label class="form-label">Kod</label><input name="kod_pocztowy" class="form-control" value="{{ old('kod_pocztowy') }}"></div>
            <div class="col-md-3"><label class="form-label">Miasto</label><input name="miasto" class="form-control" value="{{ old('miasto') }}"></div>
            <div class="col-md-3"><label class="form-label">Kraj</label><input name="kraj" class="form-control" value="{{ old('kraj','Polska') }}"></div>
        </div>

        <hr><h6>Pozycje (do 5 bez JS)</h6>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead><tr><th>Lp</th><th>Nazwa</th><th class="text-end">Ilość</th><th class="text-end">Cena netto</th><th class="text-end">VAT %</th></tr></thead>
                <tbody>
                @for ($i=0;$i<5;$i++)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td><input name="nazwa_produktu[]" class="form-control" value="{{ old('nazwa_produktu.'.$i) }}"></td>
                    <td><input name="ilosc[]" type="number" step="0.01" min="0" class="form-control text-end" value="{{ old('ilosc.'.$i) }}"></td>
                    <td><input name="cena_netto[]" type="number" step="0.01" min="0" class="form-control text-end" value="{{ old('cena_netto.'.$i) }}"></td>
                    <td><input name="stawka_vat[]" type="number" step="0.01" min="0" class="form-control text-end" value="{{ old('stawka_vat.'.$i,'23.00') }}"></td>
                </tr>
                @endfor
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <button class="btn btn-primary">Zapisz fakturę</button>
            <a href="{{ route('panel') }}" class="btn btn-outline-secondary">Anuluj</a>
        </div>
    </form>
</div>
@endsection
