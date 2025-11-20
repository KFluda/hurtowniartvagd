@extends('layouts.app')
@section('title', 'Oferta Hurtowni')

@section('content')
<div class="container py-4">

    <h1 class="h3 mb-4">Oferta Hurtowni RTV/AGD</h1>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- FILTRY / WYSZUKIWARKA --}}
    <form method="get" class="row g-2 mb-4">

        {{-- SZUKAJ --}}
        <div class="col-md-3">
            <input
                type="text"
                name="q"
                value="{{ $q }}"
                class="form-control"
                placeholder="Szukaj produktu (nazwa / SKU)...">
        </div>

        {{-- KATEGORIA --}}
        <div class="col-md-2">
            <select name="kategoria" class="form-select">
                <option value="">Wszystkie kategorie</option>
                @foreach($kategorie as $kat)
                <option value="{{ $kat->id_kategorii }}"
                        {{ (string)$kategoria === (string)$kat->id_kategorii ? 'selected' : '' }}>
                    {{ $kat->nazwa }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- PRODUCENT --}}
        <div class="col-md-2">
            <select name="producent" class="form-select">
                <option value="">Wszyscy producenci</option>
                @foreach($producenci as $pr)
                <option value="{{ $pr->id_producenta }}"
                        {{ (string)$producent === (string)$pr->id_producenta ? 'selected' : '' }}>
                    {{ $pr->nazwa }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- CENA MIN --}}
        <div class="col-md-1">
            <input type="number" step="0.01" name="cena_min"
                   class="form-control" placeholder="Min"
                   value="{{ $cena_min }}">
        </div>

        {{-- CENA MAX --}}
        <div class="col-md-1">
            <input type="number" step="0.01" name="cena_max"
                   class="form-control" placeholder="Max"
                   value="{{ $cena_max }}">
        </div>

        {{-- SORTOWANIE --}}
        <div class="col-md-2">
            <select name="sort" class="form-select">
                <option value="">Sortowanie domyślne</option>
                <option value="price_asc"  {{ $sort === 'price_asc'  ? 'selected' : '' }}>Cena rosnąco</option>
                <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Cena malejąco</option>
                <option value="name_asc"   {{ $sort === 'name_asc'   ? 'selected' : '' }}>Nazwa A–Z</option>
                <option value="name_desc"  {{ $sort === 'name_desc'  ? 'selected' : '' }}>Nazwa Z–A</option>
                <option value="newest"     {{ $sort === 'newest'     ? 'selected' : '' }}>Najnowsze</option>
            </select>
        </div>

        {{-- CZY WYMAGA NUMERU SERYJNEGO --}}
        <div class="col-md-1">
            <select name="seryjny" class="form-select">
                <option value="">Seryjny?</option>
                <option value="1" {{ $seryjny === '1' ? 'selected' : '' }}>Tak</option>
                <option value="0" {{ $seryjny === '0' ? 'selected' : '' }}>Nie</option>
            </select>
        </div>

        {{-- PRZYCISK FILTRUJ --}}
        <div class="col-12 text-end mt-2">
            <button class="btn btn-primary">
                <i class="bi bi-funnel"></i> Filtruj
            </button>
            <a href="{{ route('sklep') }}" class="btn btn-link">
                Wyczyść filtry
            </a>
        </div>
    </form>

    {{-- Lista produktów --}}
    <div class="row g-3">
        @forelse($produkty as $p)
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex flex-column">

                    {{-- Nazwa --}}
                    <h5 class="card-title">{{ $p->nazwa }}</h5>

                    {{-- Info dodatkowe --}}
                    <p class="small text-muted mb-2">
                        Kod SKU: <strong>{{ $p->kod_sku }}</strong><br>
                        Kategoria: {{ $p->kategoria_nazwa ?? '—' }}<br>
                        Producent: {{ $p->producent_nazwa ?? '—' }}<br>
                        @if($p->czy_z_numerem_seryjnym)
                        Wymaga numeru seryjnego
                        @else
                        Bez numeru seryjnego
                        @endif
                    </p>

                    <div class="mt-auto">
                        {{-- Cena brutto --}}
                        <div class="fw-bold text-primary mb-2">
                            {{ number_format((float)$p->cena_brutto, 2, ',', ' ') }} zł
                        </div>

                        <div class="d-flex gap-2">
                            {{-- Szczegóły produktu --}}
                            <a href="{{ route('produkt.show', $p->id_produktu) }}"
                               class="btn btn-outline-primary btn-sm">
                                Zobacz
                            </a>

                            {{-- Dodaj do koszyka --}}
                            <form method="POST" action="{{ route('koszyk.add') }}">
                                @csrf
                                <input type="hidden" name="produkt_id" value="{{ $p->id_produktu }}">
                                <button class="btn btn-success btn-sm">
                                    <i class="bi bi-bag-plus"></i> Dodaj
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        @empty
        <p class="text-muted">Brak produktów spełniających kryteria filtrów.</p>
        @endforelse
    </div>

    {{-- Paginacja --}}
    <div class="mt-3">
        {{ $produkty->links() }}
    </div>

</div>
@endsection
