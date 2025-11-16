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

{{-- GÓRNY PASEK --}}
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container">

        {{-- Logo: dla gościa -> strona główna, dla zalogowanego -> panel --}}
        <a class="navbar-brand fw-bold text-primary"
           href="{{ auth()->check() ? route('panel') : route('home') }}">
            Hurtownia RTV/AGD
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <div class="ms-auto d-flex align-items-center gap-2 flex-wrap">

                @auth
                {{-- Dla zalogowanych: Panel + Sklep + O nas + Kontakt + info + Wyloguj --}}
                <a href="{{ route('panel') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-speedometer2"></i> Panel
                </a>

                <a href="{{ route('sklep') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-shop"></i> Sklep
                </a>

                <a href="{{ route('pages.o-nas') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-info-circle"></i> O nas
                </a>

                <a href="{{ route('kontakt.form') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-envelope"></i> Kontakt
                </a>
                {{-- ... w sekcji @auth --}}


                <a href="{{ route('koszyk') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-bag"></i> Koszyk
                </a>


                <span class="small text-muted ms-2">
                        Zalogowany jako:
                        <strong>{{ Auth::user()->imie_nazwisko ?? Auth::user()->email }}</strong>
                    </span>

                <form action="{{ route('logout') }}" method="POST" class="d-inline ms-2">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Wyloguj
                    </button>
                </form>
                @else
                {{-- Dla gości: Sklep + O nas + Kontakt + Zaloguj --}}
                <a href="{{ route('sklep') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-shop"></i> Sklep
                </a>

                <a href="{{ route('pages.o-nas') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-info-circle"></i> O nas
                </a>

                <a href="{{ route('kontakt.form') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-envelope"></i> Kontakt
                </a>

                <a href="{{ route('koszyk') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-bag"></i> Koszyk
                </a>


                <a href="{{ route('login') }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-box-arrow-in-right"></i> Zaloguj
                </a>
                @endauth

            </div>
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
