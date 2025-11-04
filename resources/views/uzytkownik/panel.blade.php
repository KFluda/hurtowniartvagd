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
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('panel') ? 'active' : '' }}" href="{{ route('panel') }}">Panel</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('produkty.*') ? 'active' : '' }}" href="{{ route('produkty.index') }}">Produkty</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('faktury.*') ? 'active' : '' }}" href="{{ route('faktury.index') }}">Faktury sprzedaży</a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('zamowienia.create') ? 'active' : '' }}" href="{{ route('zamowienia.create') }}">Stwórz zamówienie</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('zamowienia.index') ? 'active' : '' }}" href="{{ route('zamowienia.index') }}">Zamówienia</a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('magazyn.*') ? 'active' : '' }}" href="{{ route('magazyn.stany') }}">Magazyn</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('klienci.*') ? 'active' : '' }}" href="{{ route('klienci.index') }}">Klienci</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dostawcy.*') ? 'active' : '' }}" href="{{ route('dostawcy.index') }}">Dostawcy</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('raporty.*') ? 'active' : '' }}" href="{{ route('raporty.index') }}">Raporty</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('ustawienia.*') ? 'active' : '' }}" href="{{ route('ustawienia.index') }}">Ustawienia</a>
        </li>

        @auth
        @if (strtoupper(auth()->user()->rola ?? '') === 'ADMIN')
        <li class="nav-item ms-2">
            <a class="nav-link {{ request()->routeIs('uzytkownicy.*') ? 'active' : '' }}"
               href="{{ route('uzytkownicy.index') }}">
                Użytkownicy
            </a>
        </li>
        @endif
        @endauth
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
                    <p class="text-muted">Wyszukuj po nazwie, SKU, EAN. Przeglądaj statusy i stany.</p>
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

        {{-- ZMIANA: "Zamówienia zakupu" -> "Stwórz zamówienie" --}}
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Stwórz zamówienie</h5>
                    <p class="text-muted">Wybierz klienta i dodaj pozycje z katalogu produktów.</p>
                    <a href="{{ route('zamowienia.create') }}" class="btn btn-outline-success">Przejdź</a>
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

        {{-- ZMIANA: "Ruchy magazynowe" -> "Zamówienia" (lista) --}}
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Zamówienia</h5>
                    <p class="text-muted">Lista wszystkich zamówień z wyszukiwarką.</p>
                    <a href="{{ route('zamowienia.index') }}" class="btn btn-outline-primary">Przejdź</a>
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
