@extends('layouts.app')
@section('title', 'Oferta Hurtowni')

@section('content')
<div class="container py-4">

    <h1 class="h3 mb-4">Oferta Hurtowni RTV/AGD</h1>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Wyszukiwarka --}}
    <form method="get" class="mb-3">
        <div class="input-group">
            <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Szukaj produktu...">
            <button class="btn btn-outline-primary">Szukaj</button>
        </div>
    </form>

    {{-- Lista produktów --}}
    <div class="row g-3">
        @forelse($produkty as $p)
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex flex-column">
                    {{-- Nazwa produktu --}}
                    <h5 class="card-title">{{ $p->nazwa }}</h5>

                    {{-- Opis (jeśli brak kolumny w bazie, nie pokaże błędu) --}}
                    <p class="small text-muted mb-2">
                        {{ isset($p->opis) ? $p->opis : 'Brak opisu' }}
                    </p>

                    {{-- Cena brutto --}}
                    <div class="mt-auto">
                        <div class="fw-bold text-primary mb-2">
                            {{ $p->cena_brutto !== null ? number_format($p->cena_brutto, 2, ',', ' ') . ' zł' : '—' }}
                        </div>

                        {{-- Link do szczegółów (można dodać stronę produktu później) --}}
                        <a href="#" class="btn btn-outline-primary btn-sm">Zobacz</a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <p class="text-muted">Brak produktów do wyświetlenia.</p>
        @endforelse
    </div>

    {{-- Paginacja --}}
    <div class="mt-3">
        {{ $produkty->links() }}
    </div>

</div>
@endsection
