@extends('layouts.app')
@section('title', 'Płatność / Dostawa')

@section('content')
<div class="container py-4" style="max-width: 700px">

    <h1 class="h4 mb-4">Podsumowanie zamówienia</h1>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="mb-3">
        <strong>Kwota do zapłaty:</strong>
        <span class="fs-4">{{ number_format($suma, 2, ',', ' ') }} zł</span>
    </div>

    <form method="POST" action="{{ route('platnosc.blik.pay') }}">
        @csrf

        {{-- DANE ODBIORCY --}}
        <div class="card mb-3">
            <div class="card-header">Dane do dostawy</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label" for="imie_nazwisko">Imię i nazwisko</label>
                    <input type="text"
                           id="imie_nazwisko"
                           name="imie_nazwisko"
                           class="form-control"
                           value="{{ old('imie_nazwisko') }}"
                           required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Adres e-mail</label>
                    <input type="email"
                           id="email"
                           name="email"
                           class="form-control"
                           value="{{ old('email') }}"
                           required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label" for="ulica">Ulica i nr domu / lokalu</label>
                    <input type="text"
                           id="ulica"
                           name="ulica"
                           class="form-control"
                           value="{{ old('ulica') }}"
                           required>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="kod_pocztowy">Kod pocztowy</label>
                        <input type="text"
                               id="kod_pocztowy"
                               name="kod_pocztowy"
                               class="form-control"
                               maxlength="6"
                               placeholder="00-000"
                               value="{{ old('kod_pocztowy') }}"
                               required>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label" for="miasto">Miejscowość</label>
                        <input type="text"
                               id="miasto"
                               name="miasto"
                               class="form-control"
                               value="{{ old('miasto') }}"
                               required>
                    </div>
                </div>
            </div>
        </div>

        {{-- METODA DOSTAWY --}}
        <div class="card mb-3">
            <div class="card-header">Metoda dostawy</div>
            <div class="card-body">
                @php
                $dostawaOld = old('dostawa', 'kurier');
                @endphp

                <div class="form-check">
                    <input class="form-check-input"
                           type="radio"
                           name="dostawa"
                           id="dostawa_kurier"
                           value="kurier"
                           {{ $dostawaOld === 'kurier' ? 'checked' : '' }}>
                    <label class="form-check-label" for="dostawa_kurier">
                        Kurier (1–3 dni)
                    </label>
                </div>

                <div class="form-check">
                    <input class="form-check-input"
                           type="radio"
                           name="dostawa"
                           id="dostawa_odbior"
                           value="odbior"
                           {{ $dostawaOld === 'odbior' ? 'checked' : '' }}>
                    <label class="form-check-label" for="dostawa_odbior">
                        Odbiór osobisty
                    </label>
                </div>
            </div>
        </div>

        {{-- METODA PŁATNOŚCI --}}
        <div class="card mb-3">
            <div class="card-header">Metoda płatności</div>
            <div class="card-body">
                @php
                $platnoscOld = old('platnosc', 'blik');
                @endphp

                <div class="form-check">
                    <input class="form-check-input"
                           type="radio"
                           name="platnosc"
                           id="platnosc_blik"
                           value="blik"
                           {{ $platnoscOld === 'blik' ? 'checked' : '' }}>
                    <label class="form-check-label" for="platnosc_blik">
                        BLIK
                    </label>
                </div>

                <div class="form-check">
                    <input class="form-check-input"
                           type="radio"
                           name="platnosc"
                           id="platnosc_przelew"
                           value="przelew"
                           {{ $platnoscOld === 'przelew' ? 'checked' : '' }}>
                    <label class="form-check-label" for="platnosc_przelew">
                        Przelew tradycyjny
                    </label>
                </div>

                <div class="form-check">
                    <input class="form-check-input"
                           type="radio"
                           name="platnosc"
                           id="platnosc_pobranie"
                           value="pobranie"
                           {{ $platnoscOld === 'pobranie' ? 'checked' : '' }}>
                    <label class="form-check-label" for="platnosc_pobranie">
                        Za pobraniem
                    </label>
                </div>

                {{-- Pole BLIK (widoczne tylko gdy wybrano BLIK) --}}
                <div class="mt-3"
                     id="blik-box"
                     style="{{ $platnoscOld === 'blik' ? '' : 'display:none;' }}">
                    <label for="blik" class="form-label">Kod BLIK (6 cyfr)</label>
                    <input type="text"
                           id="blik"
                           name="blik"
                           maxlength="6"
                           class="form-control text-center"
                           style="font-size:2rem; letter-spacing:8px;"
                           value="{{ old('blik') }}">
                    @error('blik')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>

                {{-- DANE DO PRZELEWU (widoczne tylko przy przelewie) --}}
                <div id="dane-przelewu"
                     class="alert alert-info mt-3"
                     style="{{ $platnoscOld === 'przelew' ? '' : 'display:none;' }}">
                    <strong>Dane do przelewu:</strong><br>
                    Hurtownia RTV/AGD Sp. z o.o.<br>
                    ul. Przykładowa 1, 00-000 Warszawa<br>
                    Numer rachunku: <strong>00 0000 0000 0000 0000 0000 0000</strong><br>
                    Tytuł przelewu: <strong>Numer zamówienia / Imię i nazwisko</strong><br>
                    <br>
                    Zamówienie będzie realizowane po
                    <strong>wpływie środków na konto hurtowni</strong>.
                </div>
            </div>
        </div>

        {{-- SUWAK --}}
        @php
        $distanceOld = old('distance', 50);
        $distanceInt = (int)$distanceOld;

        if ($distanceInt <= 50) {
        $dniPogladowo = 1;
        } elseif ($distanceInt <= 200) {
        $dniPogladowo = 2;
        } else {
        $dniPogladowo = 2 + (int)ceil(($distanceInt - 200) / 200);
        }
        @endphp

        <div class="card mb-3">
            <div class="card-header">Szacowany czas dostawy</div>
            <div class="card-body">
                <label for="distance" class="form-label">Odległość do klienta (km)</label>

                <input type="range"
                       class="form-range"
                       id="distance"
                       name="distance"
                       min="1"
                       max="500"
                       step="10"
                       value="{{ $distanceOld }}">

                <div class="mt-2">
                    Wybrana odległość:
                    <strong id="distance-km">{{ $distanceOld }} km</strong><br>
                    Szacowany czas dostawy:
                    <strong id="distance-days">{{ $dniPogladowo }} dni robocze</strong>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <a href="{{ route('koszyk') }}" class="btn btn-outline-secondary">← Wróć do koszyka</a>
            <button class="btn btn-success btn-lg">Złóż zamówienie i zapłać</button>
        </div>

    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        /* MASKA KODU POCZTOWEGO 00-000 */
        const kod = document.getElementById('kod_pocztowy');

        kod.addEventListener('input', function () {
            let v = this.value.replace(/[^0-9]/g, '').slice(0, 5);
            if (v.length >= 3) {
                this.value = v.slice(0, 2) + '-' + v.slice(2);
            } else {
                this.value = v;
            }
        });

        /* SUWAK */
        const distanceInput = document.getElementById('distance');
        const distanceKm = document.getElementById('distance-km');
        const distanceDays = document.getElementById('distance-days');

        function updateDistance() {
            const val = parseInt(distanceInput.value, 10);

            let days;
            if (val <= 50) days = 1;
            else if (val <= 200) days = 2;
            else days = 2 + Math.ceil((val - 200) / 200);

            distanceKm.textContent = val + ' km';
            distanceDays.textContent = days + ' dni robocze';
        }

        distanceInput.addEventListener('input', updateDistance);
        updateDistance();

        /* BLIK / PRZELEW */
        const radios = document.querySelectorAll('input[name="platnosc"]');
        const danePrzelewu = document.getElementById('dane-przelewu');
        const blikBox = document.getElementById('blik-box');

        function updatePayment(value) {
            danePrzelewu.style.display = (value === 'przelew') ? '' : 'none';
            blikBox.style.display = (value === 'blik') ? '' : 'none';
        }

        radios.forEach(r => {
            r.addEventListener('change', function () {
                updatePayment(this.value);
            });
        });

        const current = document.querySelector('input[name="platnosc"]:checked');
        if (current) updatePayment(current.value);
    });
</script>
@endsection
