@extends('layouts.app')
@section('title', $produkt->nazwa)

@section('content')
<div class="container py-4">
    <div class="row">
        <h1 class="h4 mb-3">{{ $produkt->nazwa }}</h1>
        <p>{{ $produkt->opis ?? 'Brak szczegółowego opisu. '}}</p>
        <div class="fs-5 text-primary fw-bold mb-4">{{ number_format($produkt->cena_brutto,2,',',' ') }} zł </div>

    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3">Zapytaj o produkt</h5>
                <form method="post" action="{{ route(frontend.order) }}">
                    @csrf
                    <input type="hidden" name="produkt_id" value="{{ $produkt->id_produktu }}">
                    <div class="mb-3">
                        <label class="form-label">Imię i nazwisko*</label>
                        <input name="nazwa" class="form-control" required>

                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email*</label>
                        <input name="email" type="email" class="form-control" required>

                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telefon</label>
                        <input name="telefon" class="form-control">

                    </div>
                    <div class="mb-3">
                        <label class="form-label">Wiadomosc</label>
                        <textarea name="wiadomosc" class="form-control" rows="3" required></textarea>

                    </div>
                    <button class="btn btn-primary" Wyślij zapytanie</button>


                </form>
            </div>
        </div>
    </div>
</div>
@endsection
