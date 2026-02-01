@extends('layouts.app')
@section('title', $produkt->nazwa)

@section('content')
<div class="container py-4">

    <a href="{{ route('sklep') }}" class="btn btn-link mb-3">
        &laquo; Powrót do sklepu
    </a>

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <div class="row g-4 align-items-start">

                {{-- ================= LEWA KOLUMNA – ZDJĘCIE PRODUKTU ================= --}}
                <div class="col-md-5">

                    @php
                    // BAZOWY adres do katalogu ze zdjęciami – TEN, KTÓRY U CIEBIE DZIAŁA
                    $imgBase = 'http://localhost/hurtownia/public/images/';

                    // mapowanie: nazwa kategorii z bazy -> nazwa pliku w katalogu /public/images
                    $localCategoryImages = [
                    'Ekspresy do kawy'      => 'ekspres.jpg',
                    'Telewizory'            => 'telewizor.jpg',
                    'Lodówki'               => 'lodowka.jpg',
                    'Pralki'                => 'pralka.jpg',
                    'Zmywarki'              => 'zmywarka.jpg',
                    'Mikrofalówki'          => 'mikrofalowka.jpg',
                    'Odkurzacze'            => 'odkurzacz.jpg',
                    'Audio / Soundbary'     => 'audio.jpg',
                    'Smartfony'             => 'smartfon.jpg',
                    'Komputery'             => 'komputery.jpg',
                    ];

                    // 1) jeżeli produkt ma swoje zdjęcie (kolumna image)
                    if (!empty($produkt->image)) {
                    $imageSrc = $imgBase . $produkt->image;
                    }
                    // 2) jeżeli nie ma -> zdjęcie z katalogu /images na podstawie kategorii
                    elseif (isset($localCategoryImages[$produkt->kategoria])) {
                    $imageSrc = $imgBase . $localCategoryImages[$produkt->kategoria];
                    }
                    // 3) ostatecznie -> placeholder z /images
                    else {
                    $imageSrc = $imgBase . 'placeholder-product.png';
                    }
                    @endphp

                    <div class="border rounded-3 overflow-hidden">
                        <img src="{{ $imageSrc }}"
                             alt="{{ $produkt->nazwa }}"
                             class="img-fluid w-100">
                    </div>

                </div>

                {{-- ================= PRAWA KOLUMNA – OPIS ================= --}}
                <div class="col-md-7">

                    <h1 class="h4 mb-3">{{ $produkt->nazwa }}</h1>

                    <p class="mb-1">
                        @if($produkt->producent)
                        <strong>Producent:</strong> {{ $produkt->producent }}<br>
                        @endif

                        <strong>Kod SKU:</strong>
                        <span class="text-monospace">{{ $produkt->kod_sku }}</span><br>

                        @if($produkt->ean)
                        <strong>EAN:</strong>
                        <span class="text-monospace">{{ $produkt->ean }}</span><br>
                        @endif

                        @if($produkt->kategoria)
                        <strong>Kategoria:</strong> {{ $produkt->kategoria }}<br>
                        @endif
                    </p>

                    <hr>

                    <p class="mb-1">
                        <strong>Cena netto:</strong>
                        {{ $produkt->cena_netto ? number_format($produkt->cena_netto, 2, ',', ' ') . ' zł' : '—' }}
                    </p>

                    <p class="mb-1">
                        <strong>Stawka VAT:</strong>
                        {{ $produkt->stawka_vat ? $produkt->stawka_vat . '%' : '—' }}
                    </p>

                    <p class="mb-3 fs-4">
                        <strong>Cena brutto:</strong>
                        @if($produkt->cena_brutto)
                        <span class="text-primary">
                                {{ number_format($produkt->cena_brutto, 2, ',', ' ') }} zł
                            </span>
                        @else
                        —
                        @endif
                    </p>

                    <p class="mb-2">
                        <strong>Dostępność:</strong>
                        @if((int)$produkt->ilosc > 0)
                        <span class="text-success">{{ (int)$produkt->ilosc }} szt. na magazynie</span>
                        @else
                        <span class="text-danger">Chwilowo niedostępny</span>
                        @endif
                    </p>

                    <p class="mb-3">
                        <strong>Gwarancja:</strong>
                        {{ $produkt->gwarancja_miesiecy ?? '—' }} mies.
                    </p>

                    {{-- ================= FORMULARZ KOSZYKA ================= --}}

                    {{-- komunikaty --}}
                    @if(session('error'))
                    <div class="alert alert-danger mb-3">
                        {{ session('error') }}
                    </div>
                    @endif

                    @if(session('success'))
                    <div class="alert alert-success mb-3">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if((int)$produkt->ilosc > 0)
                    <form method="post" action="{{ route('koszyk.add') }}" class="d-flex gap-2 align-items-center">
                        @csrf
                        <input type="hidden" name="produkt_id" value="{{ $produkt->id_produktu }}">

                        <input
                            type="number"
                            name="ilosc"
                            value="1"
                            min="1"
                            max="{{ (int)$produkt->ilosc }}"
                            class="form-control w-auto"
                        >

                        <button class="btn btn-success">
                            <i class="bi bi-cart-plus"></i> Dodaj do koszyka
                        </button>
                    </form>
                    @else
                    <div class="alert alert-warning mb-0">
                        Produkt chwilowo niedostępny.
                    </div>
                    @endif

                    <hr>

                </div>
            </div>

        </div>
    </div>

</div>
@endsection
