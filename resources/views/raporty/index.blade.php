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

        <div class="col-md-1">
            <button class="btn btn-primary w-100" id="applyBtn">Zastosuj</button>
        </div>

        <div class="col-md-2">
            <label class="form-label">TOP produkty – wg</label>
            <select name="rankBy" class="form-select">
                <option value="qty"   @selected($defaultRankBy==='qty')>Ilości</option>
                <option value="value" @selected($defaultRankBy==='value')>Wartości</option>
            </select>
        </div>

        <div class="col-md-1">
            <label class="form-label">Limit</label>
            <input type="number" name="limit" class="form-control" value="{{ $defaultLimit }}" min="3" max="50">
        </div>
    </form>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Obrót</h5>
                    <canvas id="chartObrot" height="140"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Liczba zamówień</h5>
                    <canvas id="chartZamowienia" height="140"></canvas>
                </div>
            </div>
        </div>

        {{-- NOWY WYKRES: Dochód wg okresu --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Dochód wg okresu</h5>
                    <canvas id="chartDochody" height="140"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12">
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

        let chartObrot, chartZam, chartTop, chartDoch;

        const fetchJSON = async (url, params) => {
            const qs  = new URLSearchParams(params).toString();
            const res = await fetch(url + '?' + qs, { headers: { 'X-Requested-With':'XMLHttpRequest' }});
            return res.json();
        };

        const currentParams = () => ({
            period: f.period.value,
            from:   f.from.value,
            to:     f.to.value,
            rankBy: f.rankBy.value,
            limit:  f.limit.value
        });

        const loadCharts = async () => {
            const params = currentParams();

            const obrot  = await fetchJSON('{{ route('raporty.data.obrot') }}', params);
            const zam    = await fetchJSON('{{ route('raporty.data.zamowienia') }}', params);
            const dochod = await fetchJSON('{{ route('raporty.data.dochod') }}', params); // NOWE
            const top    = await fetchJSON('{{ route('raporty.data.topProdukty') }}', params);

            // Obrót
            chartObrot && chartObrot.destroy();
            chartObrot = new Chart(chartObrotCtx, {
                type: 'line',
                data: {
                    labels: obrot.labels,
                    datasets: [{
                        label: 'Obrót (brutto)',
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

            // Liczba zamówień
            chartZam && chartZam.destroy();
            chartZam = new Chart(chartZamCtx, {
                type: 'bar',
                data: {
                    labels: zam.labels,
                    datasets: [{
                        label: 'Zamówienia (szt.)',
                        data: zam.values,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true } }
                }
            });

            // NOWY WYKRES: Dochód wg okresu
            chartDoch && chartDoch.destroy();
            chartDoch = new Chart(chartDochCtx, {
                type: 'bar',
                data: {
                    labels: dochod.labels,
                    datasets: [{
                        label: 'Dochód (suma brutto)',
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

            // TOP produkty
            chartTop && chartTop.destroy();
            chartTop = new Chart(chartTopCtx, {
                type: 'bar',
                data: {
                    labels: top.labels,
                    datasets: [{
                        label: (params.rankBy === 'qty' ? 'Ilość' : 'Wartość brutto'),
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

        // Pierwsze renderowanie
        loadCharts();
    });
</script>
@endsection
