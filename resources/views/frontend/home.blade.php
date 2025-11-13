@extends('layouts.app')
@section('title', 'Hurtownia RTV/AGD – Strona główna')

@section('content')
<div class="container py-5">

    {{-- HERO / Wprowadzenie --}}
    <div class="bg-white p-5 rounded-4 shadow-sm mb-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <h1 class="display-6 fw-bold mb-3">Witamy w Hurtowni RTV/AGD</h1>
                <p class="lead text-muted mb-4">
                    Jesteśmy zaufanym dostawcą sprzętu elektronicznego dla firm, sklepów i dystrybutorów.
                    Sprawdź naszą ofertę, poznaj promocje i złóż zamówienie online.
                </p>
                <div class="d-flex gap-2">
                    <a href="{{ route('sklep') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-shop"></i> Przejdź do sklepu
                    </a>
                    <a href="{{ url('/kontakt') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-envelope"></i> Kontakt
                    </a>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <img src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=900&q=80&auto=format&fit=crop"
                     alt="Hurtownia RTV/AGD" class="img-fluid rounded-4 shadow-sm">
            </div>
        </div>
    </div>

    {{-- Sekcja zalet --}}
    <div class="row g-3">
        <div class="col-md-4">
            <div class="bg-white p-4 rounded-4 shadow-sm h-100">
                <h5><i class="bi bi-truck"></i> Szybka dostawa</h5>
                <p class="text-muted">Zamówienia realizujemy w ciągu 24–48 godzin z naszego magazynu.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bg-white p-4 rounded-4 shadow-sm h-100">
                <h5><i class="bi bi-cash-stack"></i> Ceny hurtowe</h5>
                <p class="text-muted">Najlepsze ceny dla firm i partnerów handlowych.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bg-white p-4 rounded-4 shadow-sm h-100">
                <h5><i class="bi bi-graph-up"></i> Stała dostępność</h5>
                <p class="text-muted">Aktualne stany magazynowe i szybka rezerwacja produktów.</p>
            </div>
        </div>
    </div>

</div>
@endsection
