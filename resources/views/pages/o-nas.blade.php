@extends('layouts.app')
@section('title', 'O nas')

@section('content')
<div class="container py-4">

    {{-- HERO SECTION --}}
    <section class="py-5">
        <div class="container py-4">
            <div class="p-5 rounded-4 shadow-lg position-relative overflow-hidden"
                 style="
                 background: radial-gradient(circle at top left, #4dabff 0, #1d4ed8 35%, #111827 100%);
                 color: #ffffff;
             ">
                {{-- dekoracyjne kółko w tle --}}
                <div class="position-absolute rounded-circle"
                     style="
                    width: 260px;
                    height: 260px;
                    background: rgba(255,255,255,.06);
                    top: -80px;
                    right: -80px;
                 ">
                </div>

                <div class="position-relative" style="z-index: 2;">
                    <h1 class="display-5 fw-bold mb-3">
                        Poznaj naszą hurtownię<br>RTV/AGD
                    </h1>

                    <p class="lead mb-4">
                        Od ponad 15 lat dostarczamy sprzęt AGD, RTV i elektronikę użytkową do firm
                        w całej Polsce. Stawiamy na jakość, dostępność i profesjonalną obsługę.
                    </p>

                    <a href="{{ route('home') }}"
                       class="btn btn-light btn-lg px-4 fw-semibold shadow">
                        🔎 Przejdź do oferty
                    </a>
                </div>
            </div>
        </div>
    </section>



    {{-- KIM JESTEŚMY --}}
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h2 class="fw-bold mb-3">Kim jesteśmy?</h2>
            <p class="lead">
                Jesteśmy nowoczesną hurtownią RTV/AGD działającą na polskim rynku od ponad 10 lat.
                Obsługujemy zarówno małe sklepy detaliczne, jak i duże sieci sprzedaży.
            </p>
            <p>
                Naszym priorytetem jest szybka realizacja zamówień, pewność dostępności produktów oraz indywidualne
                podejście do każdego klienta. Współpracujemy z największymi producentami branży elektronicznej,
                gwarantując atrakcyjne ceny oraz legalny, pewny towar.
            </p>
            <p>
                Nasza hurtownia stale się rozwija — wprowadzamy nowe systemy, automatyzujemy procesy magazynowe
                oraz poszerzamy ofertę o najnowsze urządzenia domowe i elektroniczne.
            </p>
        </div>
        <div class="col-md-6">
            <img src="https://images.unsplash.com/photo-1593642532973-d31b6557fa68?auto=format&fit=crop&w=1200&q=80"
                 class="img-fluid rounded shadow-sm" alt="Magazyn i sprzęt AGD">

        </div>
    </div>

    {{-- DLACZEGO WARTO WYBRAĆ NAS --}}
    <div class="mb-5">
        <h2 class="fw-bold text-center mb-4">Dlaczego warto wybrać nas?</h2>
        <div class="row g-4">

            <div class="col-md-4">
                <div class="p-4 border rounded h-100 shadow-sm">
                    <h5 class="fw-bold"><i class="bi bi-truck"></i> Szybka realizacja zamówień</h5>
                    <p class="text-muted">
                        Dzięki nowoczesnym systemom logistycznym wysyłamy towar tego samego dnia,
                        a nasza dostępność magazynowa jest stale aktualizowana.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="p-4 border rounded h-100 shadow-sm">
                    <h5 class="fw-bold"><i class="bi bi-currency-exchange"></i> Konkurencyjne ceny</h5>
                    <p class="text-muted">
                        Jako bezpośredni dystrybutor współpracujemy z producentami, co pozwala oferować
                        atrakcyjne stawki nawet przy niewielkich zamówieniach.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="p-4 border rounded h-100 shadow-sm">
                    <h5 class="fw-bold"><i class="bi bi-shield-check"></i> Pewny i legalny towar</h5>
                    <p class="text-muted">
                        Wszystkie produkty pochodzą z oficjalnej dystrybucji, posiadają gwarancję oraz pełną
                        dokumentację techniczną.
                    </p>
                </div>
            </div>

        </div>
    </div>

    {{-- NASZ ZESPÓŁ --}}
    <div class="mb-5">
        <h2 class="fw-bold text-center mb-4">Nasz zespół</h2>

        <div class="row g-4 justify-content-center">

            <div class="col-md-3 text-center">
                <img src="https://images.unsplash.com/photo-1607746882042-944635dfe10e?auto=format&fit=crop&w=500&q=80"
                     class="rounded-circle mb-3 shadow" width="150" height="150" alt="CEO">
                <h5 class="fw-bold">Marek Kowalski</h5>
                <p class="text-muted">Dyrektor Generalny</p>
            </div>

            <div class="col-md-3 text-center">
                <img src="https://images.unsplash.com/photo-1599566150163-29194dcaad36?auto=format&fit=crop&w=500&q=80"
                     class="rounded-circle mb-3 shadow" width="150" height="150" alt="Sales Manager">
                <h5 class="fw-bold">Anna Nowak</h5>
                <p class="text-muted">Kierownik Działu Sprzedaży</p>
            </div>

            <div class="col-md-3 text-center">
                <img src="https://images.unsplash.com/photo-1603415526960-f7e0328c63b1?auto=format&fit=crop&w=500&q=80"
                     class="rounded-circle mb-3 shadow" width="150" height="150" alt="Warehouse Manager">
                <h5 class="fw-bold">Piotr Zieliński</h5>
                <p class="text-muted">Kierownik Magazynu</p>
            </div>

        </div>
    </div>

    {{-- STATYSTYKI --}}
    <div class="bg-light rounded-3 p-4 shadow-sm">
        <div class="row text-center">

            <div class="col-md-3">
                <h3 class="fw-bold text-primary">10+</h3>
                <p class="text-muted mb-0">Lat doświadczenia</p>
            </div>

            <div class="col-md-3">
                <h3 class="fw-bold text-primary">3500+</h3>
                <p class="text-muted mb-0">Zrealizowanych zamówień rocznie</p>
            </div>

            <div class="col-md-3">
                <h3 class="fw-bold text-primary">500+</h3>
                <p class="text-muted mb-0">Aktywnych klientów B2B</p>
            </div>

            <div class="col-md-3">
                <h3 class="fw-bold text-primary">1200+</h3>
                <p class="text-muted mb-0">Produktów w ofercie</p>
            </div>

        </div>
    </div>

</div>
@endsection
