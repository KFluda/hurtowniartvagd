<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie - Hurtownia RTV/AGD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header text-center">
                    <h4>Logowanie</h4>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('login.post') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Adres e-mail</label>
                            <input type="email" name="email" id="email" class="form-control"
                                   value="{{ old('email') }}" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Hasło</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Zaloguj</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="{{ route('register') }}" >
                           Zarejestruj się!
                        </a>
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                            <- Powrót na stronę główną
                        </a>
                    </div>
                </div>
            </div>

            <p class="text-center text-muted mt-3">
                © {{ date('Y') }} Hurtownia RTV/AGD
            </p>
        </div>
    </div>
</div>

</body>
</html>
