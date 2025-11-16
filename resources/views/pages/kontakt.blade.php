@extends('layouts.app')
@section('title', 'Kontakt – Hurtownia RTV/AGD')

@section('content')
<div class="container py-5">

    {{-- Alert po poprawnym wysłaniu --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"
                aria-label="Close"></button>
    </div>
    @endif

    {{-- Błędy walidacji --}}
    @if ($errors->any())
    <div class="alert alert-danger mb-4">
        <strong>Uwaga!</strong> Popraw zaznaczone pola w formularzu.
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="row g-4">

        {{-- LEWA KOLUMNA – dane firmy + zdjęcie --}}
        <div class="col-lg-5">

            <div class="bg-white rounded-4 shadow-sm mb-4 overflow-hidden">
                <img src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=900&q=80&auto=format&fit=crop"
                     alt="Magazyn RTV/AGD" class="img-fluid w-100" style="object-fit: cover; max-height: 240px;">

                <div class="p-4">
                    <h2 class="h4 mb-3">Skontaktuj się z nami</h2>
                    <p class="text-muted mb-3">
                        Nasi doradcy handlowi pomogą dobrać sprzęt, przygotują wycenę i odpowiedzą na wszystkie pytania.
                    </p>

                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                            ul. Hurtowa 10, 00-000 Warszawa
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-telephone-fill text-primary me-2"></i>
                            +48 600 100 200
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-envelope-fill text-primary me-2"></i>
                            kontakt@hurtownia.local
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-clock-fill text-primary me-2"></i>
                            Pon–Pt: 8:00–16:00
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Mapka / dodatkowe zdjęcie --}}
            <div class="bg-white rounded-4 shadow-sm overflow-hidden">
                <img src="https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?w=900&q=80&auto=format&fit=crop"
                     alt="Biuro handlowe" class="img-fluid w-100" style="object-fit: cover; max-height: 200px;">
                <div class="p-3 small text-muted">
                    Nasz zespół sprzedaży – zapraszamy do kontaktu telefonicznego lub mailowego.
                </div>
            </div>

        </div>

        {{-- PRAWA KOLUMNA – formularz --}}
        <div class="col-lg-7">
            <div class="bg-white p-4 p-lg-5 rounded-4 shadow-sm h-100">
                <h2 class="h4 mb-3">Formularz kontaktowy</h2>
                <p class="text-muted mb-4">
                    Napisz do nas w sprawie oferty, dostępności towaru, współpracy lub indywidualnej wyceny.
                </p>

                <form method="POST" action="{{ route('kontakt.send') }}" novalidate>
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Imię i nazwisko *</label>
                            <input type="text" name="imie_nazwisko"
                                   value="{{ old('imie_nazwisko') }}"
                                   class="form-control @error('imie_nazwisko') is-invalid @enderror"
                                   placeholder="Jan Kowalski">
                            @error('imie_nazwisko')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">E-mail *</label>
                            <input type="email" name="email"
                                   value="{{ old('email') }}"
                                   class="form-control @error('email') is-invalid @enderror"
                                   placeholder="jan.kowalski@firma.pl">
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Firma (opcjonalnie)</label>
                            <input type="text" name="firma"
                                   value="{{ old('firma') }}"
                                   class="form-control @error('firma') is-invalid @enderror"
                                   placeholder="Nazwa firmy">
                            @error('firma')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Temat *</label>
                            <input type="text" name="temat"
                                   value="{{ old('temat') }}"
                                   class="form-control @error('temat') is-invalid @enderror"
                                   placeholder="Zapytanie o ofertę hurtową">
                            @error('temat')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Wiadomość *</label>
                            <textarea name="wiadomosc" rows="6"
                                      class="form-control @error('wiadomosc') is-invalid @enderror"
                                      placeholder="Opisz, czego potrzebujesz – model, ilości, termin dostawy itp.">{{ old('wiadomosc') }}</textarea>
                            @error('wiadomosc')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">
                                Pola oznaczone * są obowiązkowe.
                            </small>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Wyślij wiadomość
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>
@endsection
