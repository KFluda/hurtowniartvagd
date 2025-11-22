@extends('layouts.app')

@section('title', 'Rejestracja')

@section('content')
<div class="container py-5 d-flex justify-content-center">
    <div class="card shadow-sm" style="max-width: 420px; width: 100%;">
        <div class="card-header text-center">
            <h4 class="mb-0">Rejestracja</h4>
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

            <form method="POST" action="{{ route('register.post') }}">
                @csrf

                <div class="mb-3">
                    <label for="imie_nazwisko" class="form-label">Imię i nazwisko</label>
                    <input id="imie_nazwisko"
                           type="text"
                           class="form-control @error('imie_nazwisko') is-invalid @enderror"
                           name="imie_nazwisko"
                           value="{{ old('imie_nazwisko') }}"
                           required autofocus>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Adres e-mail</label>
                    <input id="email"
                           type="email"
                           class="form-control @error('email') is-invalid @enderror"
                           name="email"
                           value="{{ old('email') }}"
                           required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Hasło</label>
                    <input id="password"
                           type="password"
                           class="form-control @error('password') is-invalid @enderror"
                           name="password"
                           required>
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Powtórz hasło</label>
                    <input id="password_confirmation"
                           type="password"
                           class="form-control"
                           name="password_confirmation"
                           required>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Załóż konto
                </button>
            </form>

            <div class="text-center mt-3">
                Masz już konto?
                <a href="{{ route('login') }}">Zaloguj się</a>
            </div>

            <div class="text-center mt-2">
                <a href="{{ route('home') }}" class="small">← Powrót na stronę główną</a>
            </div>
        </div>
    </div>
</div>
@endsection
