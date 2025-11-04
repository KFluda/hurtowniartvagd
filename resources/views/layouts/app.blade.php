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

{{-- GÓRNY PASEK z logo + wylogowaniem --}}
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="{{ route('panel') }}">
            Hurtownia RTV/AGD
        </a>

        <div class="ms-auto d-flex align-items-center">
            @auth
            <span class="me-3 small text-muted">
                    Zalogowany jako:
                    <strong>{{ Auth::user()->imie_nazwisko ?? Auth::user()->email }}</strong>
                </span>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Wyloguj
                </button>
            </form>
            @endauth
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
