@extends('layouts.app')
@section('title', $produkt->nazwa)

@section('content')
<div class="container py-4">

    <a href="{{ route('sklep') }}" class="btn btn-link mb-3">
        &laquo; Powrót do sklepu
    </a>

    <div class="row g-4">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-3">{{ $produkt->nazwa }}</h1>

                    <p class="text-muted">
                        Kod SKU: <strong>{{ $produkt->kod_sku }}</strong>
                    </p>

                    <p class="mb-2">
                        Cena netto: {{ number_format((float)$produkt->cena_netto, 2, ',', ' ') }} zł
                    </p>
                    <p class="mb-3">
                        Stawka VAT: {{ (float)$produkt->stawka_vat }}%
                    </p>
                    <h4 class="text-primary">
                        Cena brutto: {{ number_format((float)$produkt->cena_brutto, 2, ',', ' ') }} zł
                    </h4>
                    <form action="{{ route('koszyk.add') }}" method="POST" class="mt-3">
                        @csrf
                        <input type="hidden" name="produkt_id" value="{{ $produkt->id_produktu }}">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-bag-plus"></i> Dodaj do koszyka
                        </button>
                    </form>

                    <hr>

                    <p class="text-muted mb-0">
                        Brak szczegółowego opisu produktu w bazie.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-5">

        </div>
    </div>

</div>
@endsection
