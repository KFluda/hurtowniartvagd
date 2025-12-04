@extends('layouts.app')
@section('title','Raporty')

@section('content')
<div class="container py-3">
    <h1 class="h4 mb-3">Raporty</h1>

    <form id="filters" class="row g-2 align-items-end mb-3">
        <div class="col-md-3">
            <label class="form-label">Okres</label>
            <select name="period" id="period" class="form-select">
                <option value="day"     @selected($defaultPeriod==='day')>Dzień</option>
                <option value="week"    @selected($defaultPeriod==='week')>Tydzień</option>
                <option value="month"   @selected($defaultPeriod==='month')>Miesiąc</option>
                <option value="quarter" @selected($defaultPeriod==='quarter')>Kwartał</option>
                <option value="year"    @selected($defaultPeriod==='year')>Rok</option>
                <option value="range"   @selected($defaultPeriod==='range')>Własny zakres</option>
            </select>
        </div>

        {{-- pola Od / Do pokazywane tylko dla "Własny zakres" --}}
        <div class="col-md-3 js-date-range {{ $defaultPeriod !== 'range' ? 'd-none' : '' }}">
            <label class="form-label">Od</label>
            <input type="date" name="from" class="form-control" value="{{ $defaultFrom }}">
        </div>

        <div class="col-md-3 js-date-range {{ $defaultPeriod !== 'range' ? 'd-none' : '' }}">
            <label class="form-label">Do</label>
            <input type="date" name="to" class="form-control" value="{{ $defaultTo }}">
        </div>

        {{-- NOWY FILTR: Kategoria --}}
        <div class="col-md-3">
            <label class="form-label">Kategoria</label>
            <select name="category" id="category" class="form-select">
                <option value="">Wszystkie kategorie</option>
                @foreach($kategorie as $kat)
                <option value="{{ $kat->id_kategorii }}"
                        @selected($defaultCategory == $kat->id_kategorii)>
                    {{ $kat->nazwa }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-1">
            <button class="btn btn-primary w-100" id="applyBtn">Zastosuj</button>
        </div>

        <div class="col-md-2">
            <label class="form-label">Forma wykresów</label>
            <select name="rankBy" class="form-select">
                <option value="qty"   @selected($defaultRankBy==='qty')>Ilości</option>
                <option value="value" @selected($defaultRankBy==='value')>Wartości</option>
            </select>
        </div>

        <div class="col-md-1">
            <label class="form-label">Limit TOP produktów</label>
            <input type="number" name="limit" class="form-control" value="{{ $defaultLimit }}" min="3" max="50">
        </div>
    </form>

    <div class="row g-3">
        <div class="col-lg-6" id="cardObrot">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Obrót</h5>
                    <canvas id="chartObrot" height="140"></canvas>
                    <div id="legendObrot" class="small text-muted mt-2"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-6" id="cardZamowienia">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Liczba zamówień</h5>
                    <canvas id="chartZamowienia" height="140"></canvas>
                    <div id="legendZamowienia" class="small text-muted mt-2"></div>
                </div>
            </div>
        </div>

        {{-- Dochód wg okresu --}}
        <div class="col-12" id="cardDochody">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Dochód wg okresu</h5>
                    <canvas id="chartDochody" height="140"></canvas>
                    <div id="legendDochody" class="small text-muted mt-2"></div>
                </div>
            </div>
        </div>

        <div class="col-12" id="cardTopProdukty">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">TOP produkty</h5>
                    <canvas id="chartTopProdukty" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const f = document.querySelector('#filters');

        // --- POKAZYWANIE / UKRYWANIE pól "Od / Do" ---
        const periodSelect    = document.getElementById('period');
        const dateRangeGroups = document.querySelectorAll('.js-date-range');

        const toggleDateRange = () => {
            const show = periodSelect.value === 'range';
            dateRangeGroups.forEach(el => {
                el.classList.toggle('d-none', !show);
            });
        };

        periodSelect.addEventListener('change', toggleDateRange);
        toggleDateRange(); // ustaw od razu po załadowaniu
        // --- KONIEC BLOKU ---

        const chartObrotCtx   = document.getElementById('chartObrot').getContext('2d');
        const chartZamCtx     = document.getElementById('chartZamowienia').getContext('2d');
        const chartDochCtx    = document.getElementById('chartDochody').getContext('2d');
        const chartTopCtx     = document.getElementById('chartTopProdukty').getContext('2d');

        const cardObrot       = document.getElementById('cardObrot');
        const cardZamowienia  = document.getElementById('cardZamowienia');
        const cardDochody     = document.getElementById('cardDochody');
        const cardTopProdukty = document.getElementById('cardTopProdukty');

        let chartObrot, chartZam, chartTop, chartDoch;

        const fetchJSON = async (url, params) => {
            const qs  = new URLSearchParams(params).toString();
            const res = await fetch(url + '?' + qs, {
                headers: { 'X-Requested-With':'XMLHttpRequest' }
            });
            return res.json();
        };

        const currentParams = () => ({
            period:   f.period.value,
            from:     f.from ? f.from.value : '',
            to:       f.to ? f.to.value : '',
            category: f.category ? f.category.value : '',
            rankBy:   f.rankBy.value,
            limit:    f.limit.value
        });

        const formatPln = (value) => {
            return Number(value || 0).toLocaleString('pl-PL', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + ' zł';
        };

        const toggleCardsForRankBy = () => {
            const rankBy = f.rankBy.value;

            if (rankBy === 'qty') {

                cardObrot.classList.add('d-none');
                cardDochody.classList.add('d-none');

                cardZamowienia.classList.remove('d-none');
                cardTopProdukty.classList.remove('d-none');
            } else {

                cardZamowienia.classList.add('d-none');

                cardObrot.classList.remove('d-none');
                cardDochody.classList.remove('d-none');
                cardTopProdukty.classList.remove('d-none');
            }
        };


        const loadCharts = async () => {
            const params = currentParams();
            const period = params.period;
            const rankBy = params.rankBy;

            toggleCardsForRankBy();

            const obrot  = await fetchJSON('{{ route('raporty.data.obrot') }}', params);
            const zam    = await fetchJSON('{{ route('raporty.data.zamowienia') }}', params);
            const dochod = await fetchJSON('{{ route('raporty.data.dochod') }}', params);
            const top    = await fetchJSON('{{ route('raporty.data.topProdukty') }}', params);

            // ========= OBRÓT =========
            chartObrot && chartObrot.destroy();
            chartObrot = new Chart(chartObrotCtx, {
                type: 'line',
                data: {
                    labels: obrot.labels,
                    datasets: [{
                        label:
                            period === 'year'  ? 'Obrót miesięczny (brutto)'  :
                                period === 'month' ? 'Obrót tygodniowy (brutto)' :
                                    period === 'week'  ? 'Obrót dzienny (brutto)'    :
                                        'Obrót (brutto)',
                        data: obrot.values,
                        borderWidth: 2,
                        tension: .2
                    }]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true } }
                }
            });

            const legendObrot = document.getElementById('legendObrot');
            if (!obrot.labels.length) {
                legendObrot.textContent = 'Brak danych w wybranym okresie.';
            } else {
                const entries = obrot.labels.map((label, i) => {
                    const val = obrot.values[i] ?? 0;
                    return `${label}: ${formatPln(val)}`;
                });
                legendObrot.textContent = 'Słupki: ' + entries.join('  |  ');
            }

            // ========= LICZBA ZAMÓWIEŃ =========
            chartZam && chartZam.destroy();
            chartZam = new Chart(chartZamCtx, {
                type: 'bar',
                data: {
                    labels: zam.labels,
                    datasets: [{
                        label:
                            period === 'year'  ? 'Zamówienia (miesięcznie)' :
                                period === 'month' ? 'Zamówienia (tygodniowo)'  :
                                    period === 'week'  ? 'Zamówienia (dziennie)'    :
                                        'Zamówienia (szt.)',
                        data: zam.values,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true } }
                }
            });

            const legendZam = document.getElementById('legendZamowienia');
            if (!zam.labels.length) {
                legendZam.textContent = 'Brak zamówień w wybranym okresie.';
            } else {
                const entries = zam.labels.map((label, i) => {
                    const val = zam.values[i] ?? 0;
                    return `${label}: ${val} szt.`;
                });
                legendZam.textContent = 'Słupki: ' + entries.join('  |  ');
            }

            // ========= DOCHÓD WG OKRESU =========
            chartDoch && chartDoch.destroy();
            chartDoch = new Chart(chartDochCtx, {
                type: 'bar',
                data: {
                    labels: dochod.labels,
                    datasets: [{
                        label:
                            period === 'year'  ? 'Dochód miesięczny (brutto)' :
                                period === 'month' ? 'Dochód tygodniowy (brutto)' :
                                    period === 'week'  ? 'Dochód dzienny (brutto)'    :
                                        'Dochód (suma brutto)',
                        data: dochod.values,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (value) => value.toLocaleString('pl-PL') + ' zł'
                            }
                        }
                    }
                }
            });

            const legendDoch = document.getElementById('legendDochody');
            if (!dochod.labels.length) {
                legendDoch.textContent = 'Brak danych o dochodzie w wybranym okresie.';
            } else {
                const entries = dochod.labels.map((label, i) => {
                    const val = dochod.values[i] ?? 0;
                    return `${label}: ${formatPln(val)}`;
                });
                legendDoch.textContent = 'Słupki: ' + entries.join('  |  ');
            }

            // ========= TOP PRODUKTY =========
            chartTop && chartTop.destroy();
            chartTop = new Chart(chartTopCtx, {
                type: 'bar',
                data: {
                    labels: top.labels,
                    datasets: [{
                        label: (rankBy === 'qty' ? 'Ilość' : 'Wartość brutto'),
                        data: top.values
                    }]
                },
                options: {
                    responsive: true,
                    indexAxis: 'y',
                    scales: { x: { beginAtZero: true } }
                }
            });
        };

        // Zastosuj filtry
        document.getElementById('applyBtn').addEventListener('click', (e) => {
            e.preventDefault();
            loadCharts();
        });

        // reaguj na zmianę "Forma wykresów" (Ilości / Wartości)
        f.rankBy.addEventListener('change', () => {
            toggleCardsForRankBy();
            loadCharts();
        });

        // reaguj na zmianę kategorii
        if (f.category) {
            f.category.addEventListener('change', () => {
                loadCharts();
            });
        }

        // Pierwsze renderowanie
        toggleCardsForRankBy();
        loadCharts();
    });
</script>
@endsection
