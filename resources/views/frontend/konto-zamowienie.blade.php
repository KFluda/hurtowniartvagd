@extends('layouts.app')
@section('title', 'Szczegóły zamówienia')

@section('content')
<div class="container py-4" style="max-width: 900px;">

    <h1 class="h4 mb-3">
        Zamówienie {{ $zamowienie->numer_zamowienia }}
    </h1>

    <p class="mb-3">
        <a href="{{ route('konto') }}#tab-zamowienia" class="btn btn-sm btn-outline-secondary">
            ← Powrót do historii zamówień
        </a>
    </p>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">Podstawowe informacje</div>
                <div class="card-body">
                    <p><strong>Numer:</strong> {{ $zamowienie->numer_zamowienia }}</p>
                    <p><strong>Data:</strong> {{ $zamowienie->data_wystawienia }}</p>
                    <p>
                        <strong>Status:</strong>
                        <span class="badge bg-secondary">{{ $zamowienie->status }}</span>
                    </p>
                    <p><strong>Suma netto:</strong> {{ number_format($zamowienie->suma_netto, 2, ',', ' ') }} zł</p>
                    <p><strong>VAT:</strong> {{ number_format($zamowienie->suma_vat, 2, ',', ' ') }} zł</p>
                    <p><strong>Suma brutto:</strong> {{ number_format($zamowienie->suma_brutto, 2, ',', ' ') }} zł</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">Dane i uwagi</div>
                <div class="card-body">
                    <p class="mb-0" style="white-space: pre-wrap;">
                        {{ $zamowienie->uwagi }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <p class="text-muted mt-3 mb-0">
        Jeśli coś się nie zgadza – skontaktuj się z obsługą sklepu.
    </p>
</div>
@endsection

