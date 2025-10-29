@extends('layouts.app')
@section('title', $mode==='create' ? 'Dodaj produkt' : 'Edytuj produkt')

@section('content')
<div class="container py-3">
    <h1 class="h4 mb-3">{{ $mode==='create' ? 'Dodaj produkt' : 'Edytuj produkt' }}</h1>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="post"
          action="{{ $mode==='create' ? route('produkty.store') : route('produkty.update',$id) }}"
          class="bg-white border rounded-3 p-3">
        @csrf
        @if($mode==='edit') @method('PUT') @endif

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">SKU</label>
                <input name="kod_sku" class="form-control" value="{{ old('kod_sku',$produkt->kod_sku ?? '') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">EAN</label>
                <input name="ean" class="form-control" value="{{ old('ean',$produkt->ean ?? '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Nazwa</label>
                <input name="nazwa" class="form-control" value="{{ old('nazwa',$produkt->nazwa ?? '') }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Producent</label>
                <select name="id_producenta" class="form-select">
                    <option value="">— wybierz —</option>
                    @foreach($producenci as $pr)
                    <option value="{{ $pr->id_producenta }}"
                            @selected(old('id_producenta',$produkt->id_producenta ?? null)==$pr->id_producenta)>
                    {{ $pr->nazwa }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Kategoria</label>
                <select name="id_kategorii" class="form-select">
                    <option value="">— wybierz —</option>
                    @foreach($kategorie as $k)
                    <option value="{{ $k->id_kategorii }}"
                            @selected(old('id_kategorii',$produkt->id_kategorii ?? null)==$k->id_kategorii)>
                    {{ $k->nazwa }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- ZAMIANA VAT -> ILOŚĆ -->
            <div class="col-md-4">
                <label class="form-label">Stan magazynowy (ilość)</label>
                <input
                    name="ilosc"
                    type="number"
                    class="form-control"
                    min="0"
                    step="1"
                    value="{{ old('ilosc', isset($produkt) ? $produkt->ilosc : 0) }}"
                    required
                >
                <div class="form-text">Podaj aktualną ilość w magazynie (w sztukach).</div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Waga (kg)</label>
                <input name="waga_kg" type="number" step="0.001" class="form-control"
                       value="{{ old('waga_kg',$produkt->waga_kg ?? '') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Głębokość (mm)</label>
                <input name="glebokosc_mm" type="number" class="form-control"
                       value="{{ old('glebokosc_mm',$produkt->glebokosc_mm ?? '') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Szerokość (mm)</label>
                <input name="szerokosc_mm" type="number" class="form-control"
                       value="{{ old('szerokosc_mm',$produkt->szerokosc_mm ?? '') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Wysokość (mm)</label>
                <input name="wysokosc_mm" type="number" class="form-control"
                       value="{{ old('wysokosc_mm',$produkt->wysokosc_mm ?? '') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Gwarancja (mies.)</label>
                <input name="gwarancja_miesiecy" type="number" class="form-control"
                       value="{{ old('gwarancja_miesiecy',$produkt->gwarancja_miesiecy ?? '') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Numer seryjny wymagany?</label>
                <select name="czy_z_numerem_seryjnym" class="form-select">
                    <option value="0" @selected(old('czy_z_numerem_seryjnym',$produkt->czy_z_numerem_seryjnym ?? 0)==0)>nie</option>
                    <option value="1" @selected(old('czy_z_numerem_seryjnym',$produkt->czy_z_numerem_seryjnym ?? 0)==1)>tak</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="aktywny" class="form-select">
                    <option value="1" @selected(old('aktywny',$produkt->aktywny ?? 1)==1)>aktywny</option>
                    <option value="0" @selected(old('aktywny',$produkt->aktywny ?? 1)==0)>nieaktywny</option>
                </select>
            </div>
        </div>

        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary">{{ $mode==='create' ? 'Dodaj' : 'Zapisz' }}</button>
            <a href="{{ route('produkty.index') }}" class="btn btn-outline-secondary">Anuluj</a>
        </div>
    </form>
</div>
@endsection
