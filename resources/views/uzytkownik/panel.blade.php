@extends('layouts.app')
@section('title','Panel użytkownika')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Witaj, {{ $user->imie_nazwisko }}</h1>
            <div class="text-muted">Rola: {{ $user->rola }}</div>
        </div>
        <form method="get" action="{{ route('produkty.index') }}" class="d-flex gap-2">
            <input type="search" name="q" class="form-control" placeholder="Szukaj produktu (nazwa / SKU / EAN)">
            <button class="btn btn-primary">Szukaj</button>
        </form>
    </div>

    {{-- Zakładki (nawigacja) --}}
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><a class="nav-link active" aria-current="page" href="{{ route('panel') }}">Panel</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('produkty.index') }}">Produkty</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('faktury.index') }}">Faktury sprzedaży</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('zakupy.index') }}">Zamówienia zakupu</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('magazyn.stany') }}">Magazyn</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('ruchy.index') }}">Ruchy magazynowe</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('klienci.index') }}">Klienci</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('dostawcy.index') }}">Dostawcy</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('raporty.index') }}">Raporty</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('ustawienia.index') }}">Ustawienia</a></li>
    </ul>

    {{-- Szybkie statystyki --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Produkty</div>
                    <div class="fs-4 fw-bold">{{ $counts['produkty'] ?? '—' }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Magazyny</div>
                    <div class="fs-4 fw-bold">{{ $counts['magazyny'] ?? '—' }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Klienci</div>
                    <div class="fs-4 fw-bold">{{ $counts['klienci'] ?? '—' }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Dostawcy</div>
                    <div class="fs-4 fw-bold">{{ $counts['dostawcy'] ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Szybkie skróty --}}
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Katalog produktów</h5>
                    <p class="text-muted">Wyszukuj po nazwie, SKU, EAN. Przeglądaj statusy i ceny.</p>
                    <a href="{{ route('produkty.index') }}" class="btn btn-outline-primary">Przejdź</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Faktury sprzedaży</h5>
                    <p class="text-muted">Lista i wyszukiwarka dokumentów FS, pobieranie PDF (do zrobienia).</p>
                    <a href="{{ route('faktury.index') }}" class="btn btn-outline-primary">Przejdź</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Zamówienia zakupu</h5>
                    <p class="text-muted">Zamówienia do dostawców, statusy i terminy (do zrobienia).</p>
                    <a href="{{ route('zakupy.index') }}" class="btn btn-outline-primary">Przejdź</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Stany magazynowe</h5>
                    <p class="text-muted">Aktualna dostępność i rezerwacje na magazynach.</p>
                    <a href="{{ route('magazyn.stany') }}" class="btn btn-outline-primary">Przejdź</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Ruchy magazynowe</h5>
                    <p class="text-muted">Przyjęcia, wydania, korekty (do zrobienia).</p>
                    <a href="{{ route('ruchy.index') }}" class="btn btn-outline-primary">Przejdź</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Raporty</h5>
                    <p class="text-muted">Obroty, stany, rotacja, ABC (do zrobienia).</p>
                    <a href="{{ route('raporty.index') }}" class="btn btn-outline-primary">Przejdź</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
