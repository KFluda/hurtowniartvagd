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
            <input
                type="text"
                name="q"
                value="{{ $q }}"
                class="form-control"
                placeholder="Szukaj produktu (nazwa / SKU)...">
            <button class="btn btn-outline-primary">Szukaj</button>
        </div>
    </form>

    {{-- Lista produktów --}}
    <div class="row g-3">
        @forelse($produkty as $p)
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">{{ $p->nazwa }}</h5>

                    {{-- Opisu w bazie nie ma, więc proste info --}}
                    <p class="small text-muted mb-2">
                        Brak opisu
                    </p>

                    <div class="mt-auto">
                        <div class="fw-bold text-primary mb-2">
                            {{ number_format((float)$p->cena_brutto, 2, ',', ' ') }} zł
                        </div>

                        <a href="{{ route('produkt.show', $p->id_produktu) }}"
                           class="btn btn-outline-primary btn-sm">
                            Zobacz
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <p class="text-muted">Brak produktów do wyświetlenia.</p>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $produkty->links() }}
    </div>

</div>
@endsection
