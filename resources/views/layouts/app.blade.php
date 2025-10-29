<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <title>@yield('title','Hurtownia RTV/AGD')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="{{ route('panel') }}">
            Hurtownia RTV/AGD
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div id="menu" class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a href="{{ route('produkty.index') }}" class="nav-link">Produkty</a></li>
                <li class="nav-item"><a href="{{ route('magazyn.stany') }}" class="nav-link">Stany</a></li>

                {{-- NOWE NAZWY --}}
                <li class="nav-item"><a href="{{ route('zamowienia.create') }}" class="nav-link">Stwórz zamówienie</a></li>
                <li class="nav-item"><a href="{{ route('zamowienia.index') }}" class="nav-link">Zamówienia</a></li>

                <li class="nav-item"><a href="{{ route('pages.o-nas') }}" class="nav-link">O nas</a></li>
                <li class="nav-item"><a href="{{ route('pages.kontakt') }}" class="nav-link">Kontakt</a></li>
            </ul>
        </div>
    </div>
</nav>

<main class="py-4">
    @yield('content')
</main>

<footer class="border-top bg-white py-3 mt-4">
    <div class="container text-center text-muted small">
        © {{ date('Y') }} Hurtownia RTV/AGD
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
