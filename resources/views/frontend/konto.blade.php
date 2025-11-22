@extends('layouts.app')
@section('title', 'Moje konto')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Moje konto</h1>

        {{-- HAMBURGER NA MOBILE --}}
        <button class="btn btn-outline-secondary d-md-none" id="kontoMenuToggle">
            ☰ Menu
        </button>
    </div>

    <div class="row">
        {{-- LEWE MENU --}}
        <div class="col-md-3 mb-3" id="kontoSidebar">
            <div class="list-group">
                <a href="#tab-profil" class="list-group-item list-group-item-action active" data-tab="tab-profil">
                    Dane profilu
                </a>
                <a href="#tab-zamowienia" class="list-group-item list-group-item-action" data-tab="tab-zamowienia">
                    Historia zamówień
                </a>
                <a href="#tab-pomoc" class="list-group-item list-group-item-action" data-tab="tab-pomoc">
                    Pomoc
                </a>
            </div>
        </div>

        {{-- PRAWA CZĘŚĆ – ZAWARTOŚĆ ZAKŁADEK --}}
        <div class="col-md-9">

            {{-- ZAKŁADKA: PROFIL --}}
            <div id="tab-profil" class="konto-tab">
                <div class="card">
                    <div class="card-header">Dane profilu</div>
                    <div class="card-body">
                        <p><strong>Imię i nazwisko:</strong> {{ $user->imie_nazwisko }}</p>
                        <p><strong>E-mail:</strong> {{ $user->email }}</p>
                        <p><strong>Rola:</strong> {{ $user->rola }}</p>
                        <p><strong>Status konta:</strong> {{ $user->aktywny ? 'Aktywne' : 'Zablokowane' }}</p>
                    </div>
                </div>
            </div>

            {{-- ZAKŁADKA: HISTORIA ZAMÓWIEŃ --}}
            <div id="tab-zamowienia" class="konto-tab d-none">
                <div class="card">
                    <div class="card-header">Historia zamówień</div>
                    <div class="card-body">
                        @if($zamowienia->isEmpty())
                        <p class="text-muted mb-0">
                            Nie masz jeszcze żadnych zamówień.
                        </p>
                        @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                <tr>
                                    <th>Nr zamówienia</th>
                                    <th>Data</th>
                                    <th>Status</th>
                                    <th class="text-end">Suma brutto</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($zamowienia as $zam)
                                <tr>
                                    <td>{{ $zam->numer_zamowienia }}</td>
                                    <td>{{ $zam->data_wystawienia }}</td>
                                    <td>
                                                <span class="badge bg-secondary">
                                                    {{ $zam->status }}
                                                </span>
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($zam->suma_brutto, 2, ',', ' ') }} zł
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('konto.zamowienie', $zam->id_zamowienia) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            Szczegóły
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ZAKŁADKA: POMOC --}}
            <div id="tab-pomoc" class="konto-tab d-none">
                <div class="card">
                    <div class="card-header">Pomoc</div>
                    <div class="card-body">
                        <p>Masz pytania dotyczące zamówień lub dostawy?</p>
                        <p>Skontaktuj się z nami poprzez formularz
                            <a href="{{ route('kontakt.form') }}">kontaktowy</a>.
                        </p>
                        <p class="mb-0 text-muted">
                            W przyszłości możesz tu dodać FAQ, statusy płatności itp.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // HAMBURGER – chowamy/pokazujemy menu na mobile
        const toggleBtn = document.getElementById('kontoMenuToggle');
        const sidebar   = document.getElementById('kontoSidebar');

        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', function () {
                sidebar.classList.toggle('d-none');
            });
        }

        // Zakładki
        const links = document.querySelectorAll('#kontoSidebar .list-group-item');
        const tabs  = document.querySelectorAll('.konto-tab');

        links.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();

                const targetId = this.getAttribute('data-tab');

                // aktywny link
                links.forEach(l => l.classList.remove('active'));
                this.classList.add('active');

                // aktywna karta
                tabs.forEach(tab => {
                    if (tab.id === targetId) {
                        tab.classList.remove('d-none');
                    } else {
                        tab.classList.add('d-none');
                    }
                });
            });
        });
    });
</script>
@endsection

