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

        {{-- Kategoria A --}}
        <div class="col-md-3">
            <label class="form-label">Kategoria A</label>
            <select name="category" id="category" class="form-select">
                <option value="">Wszystkie kategorie</option>
                @foreach($kategorie as $kat)
                <option value="{{ $kat->id_kategorii }}"
                        @selected(($defaultCategory ?? '') == $kat->id_kategorii)>
                {{ $kat->nazwa }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Kategoria B (do porównania) --}}
        <div class="col-md-3">
            <label class="form-label">Kategoria B (porównanie)</label>
            <select name="category2" id="category2" class="form-select">
                <option value="">Brak (tylko A)</option>
                @foreach($kategorie as $kat)
                <option value="{{ $kat->id_kategorii }}"
                        @selected(($defaultCategory2 ?? '') == $kat->id_kategorii)>
                {{ $kat->nazwa }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-1">
            <label class="form-label d-block">&nbsp;</label>
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

@php
// przygotowanie mapy id_kategorii => nazwa (dla podpisów serii)
$CATEGORY_NAMES = $kategorie->pluck('nazwa', 'id_kategorii');
@endphp

<script>
    // JS-owa mapa id_kategorii -> nazwa
    var CATEGORY_NAMES = {!! json_encode($CATEGORY_NAMES) !!};

    document.addEventListener('DOMContentLoaded', function () {
        var f = document.getElementById('filters');

        // --- POKAZYWANIE / UKRYWANIE pól "Od / Do" ---
        var periodSelect    = document.getElementById('period');
        var dateRangeGroups = document.querySelectorAll('.js-date-range');

        function toggleDateRange() {
            var show = periodSelect.value === 'range';
            dateRangeGroups.forEach(function (el) {
                el.classList.toggle('d-none', !show);
            });
        }

        periodSelect.addEventListener('change', toggleDateRange);
        toggleDateRange();

        var chartObrotCtx   = document.getElementById('chartObrot').getContext('2d');
        var chartZamCtx     = document.getElementById('chartZamowienia').getContext('2d');
        var chartDochCtx    = document.getElementById('chartDochody').getContext('2d');
        var chartTopCtx     = document.getElementById('chartTopProdukty').getContext('2d');

        var cardObrot       = document.getElementById('cardObrot');
        var cardZamowienia  = document.getElementById('cardZamowienia');
        var cardDochody     = document.getElementById('cardDochody');
        var cardTopProdukty = document.getElementById('cardTopProdukty');

        var chartObrot, chartZam, chartTop, chartDoch;

        function fetchJSON(url, params) {
            var qs  = new URLSearchParams(params).toString();
            return fetch(url + '?' + qs, {
                headers: { 'X-Requested-With':'XMLHttpRequest' }
            }).then(function (res) {
                return res.json();
            });
        }

        function currentParams() {
            return {
                period:   f.period.value,
                from:     f.from ? f.from.value : '',
                to:       f.to   ? f.to.value   : '',
                category: f.category  ? f.category.value  : '',
                category2:f.category2 ? f.category2.value : '',
                rankBy:   f.rankBy.value,
                limit:    f.limit.value
            };
        }

        function formatPln(value) {
            return Number(value || 0).toLocaleString('pl-PL', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + ' zł';
        }

        function getCategoryName(id) {
            if (!id) return 'Wszystkie kategorie';
            return CATEGORY_NAMES[id] || ('Kategoria ' + id);
        }

        function mergeLabels(a, b) {
            var set = {};
            (a || []).forEach(function (v) { set[v] = true; });
            (b || []).forEach(function (v) { set[v] = true; });
            return Object.keys(set).sort();
        }

        function toggleCardsForRankBy() {
            var rankBy = f.rankBy.value;

            if (rankBy === 'qty') {
                // tryb ilości: Liczba zamówień + TOP produkty
                cardObrot.classList.add('d-none');
                cardDochody.classList.add('d-none');

                cardZamowienia.classList.remove('d-none');
                cardTopProdukty.classList.remove('d-none');
            } else {
                // tryb wartości: Obrót + Dochód + TOP produkty
                cardZamowienia.classList.add('d-none');

                cardObrot.classList.remove('d-none');
                cardDochody.classList.remove('d-none');
                cardTopProdukty.classList.remove('d-none');
            }
        }

        function loadCharts() {
            var params = currentParams();
            var period = params.period;
            var rankBy = params.rankBy;
            var cat1   = params.category;
            var cat2   = params.category2;

            toggleCardsForRankBy();

            var baseParams = {
                period: params.period,
                from:   params.from,
                to:     params.to,
                rankBy: params.rankBy,
                limit:  params.limit
            };

            Promise.all([
                fetchJSON('{{ route('raporty.data.obrot') }}',      Object.assign({}, baseParams, {category: cat1})),
                cat2 ? fetchJSON('{{ route('raporty.data.obrot') }}',      Object.assign({}, baseParams, {category: cat2})) : Promise.resolve({labels:[],values:[]}),
                fetchJSON('{{ route('raporty.data.zamowienia') }}', Object.assign({}, baseParams, {category: cat1})),
            cat2 ? fetchJSON('{{ route('raporty.data.zamowienia') }}', Object.assign({}, baseParams, {category: cat2})) : Promise.resolve({labels:[],values:[]}),
                fetchJSON('{{ route('raporty.data.dochod') }}',     Object.assign({}, baseParams, {category: cat1})),
            cat2 ? fetchJSON('{{ route('raporty.data.dochod') }}',     Object.assign({}, baseParams, {category: cat2})) : Promise.resolve({labels:[],values:[]}),
                fetchJSON('{{ route('raporty.data.topProdukty') }}',Object.assign({}, baseParams, {category: cat1})),
            cat2 ? fetchJSON('{{ route('raporty.data.topProdukty') }}',Object.assign({}, baseParams, {category: cat2})) : Promise.resolve({labels:[],values:[]})
        ]).then(function (all) {
                var obrot1 = all[0], obrot2 = all[1];
                var zam1   = all[2], zam2   = all[3];
                var doch1  = all[4], doch2  = all[5];
                var top1   = all[6], top2   = all[7];

                // ====== OBRÓT (2 linie) ======
                if (chartObrot) chartObrot.destroy();

                var labelsObrot = mergeLabels(obrot1.labels, obrot2.labels);
                var obrotData1 = labelsObrot.map(function (l) {
                    var idx = (obrot1.labels || []).indexOf(l);
                    return idx >= 0 ? obrot1.values[idx] : 0;
                });
                var obrotData2 = labelsObrot.map(function (l) {
                    var idx = (obrot2.labels || []).indexOf(l);
                    return idx >= 0 ? obrot2.values[idx] : 0;
                });

                var obrotDatasets = [{
                    label:
                        (period === 'year'  ? 'Obrót miesięczny (brutto) – '  :
                            period === 'month' ? 'Obrót tygodniowy (brutto) – ' :
                                period === 'week'  ? 'Obrót dzienny (brutto) – '    :
                                    'Obrót (brutto) – ') + getCategoryName(cat1),
                    data: obrotData1,
                    borderWidth: 2,
                    tension: 0.2,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)'
                }];

                if (cat2 && obrot2.labels.length) {
                    obrotDatasets.push({
                        label:
                            (period === 'year'  ? 'Obrót miesięczny (brutto) – '  :
                                period === 'month' ? 'Obrót tygodniowy (brutto) – ' :
                                    period === 'week'  ? 'Obrót dzienny (brutto) – '    :
                                        'Obrót (brutto) – ') + getCategoryName(cat2),
                        data: obrotData2,
                        borderWidth: 2,
                        tension: 0.2,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)'
                    });
                }

                chartObrot = new Chart(chartObrotCtx, {
                    type: 'line',
                    data: {
                        labels: labelsObrot,
                        datasets: obrotDatasets
                    },
                    options: {
                        responsive: true,
                        scales: { y: { beginAtZero: true } }
                    }
                });

                var legendObrot = document.getElementById('legendObrot');
                if (!labelsObrot.length) {
                    legendObrot.textContent = 'Brak danych w wybranym okresie.';
                } else {
                    var entries1 = labelsObrot.map(function (label, i) {
                        return label + ': ' + formatPln(obrotData1[i]);
                    });
                    var text = 'Seria ' + getCategoryName(cat1) + ': ' + entries1.join(' | ');
                    if (cat2 && obrot2.labels.length) {
                        var entries2 = labelsObrot.map(function (label, i) {
                            return label + ': ' + formatPln(obrotData2[i]);
                        });
                        text += ' || Seria ' + getCategoryName(cat2) + ': ' + entries2.join(' | ');
                    }
                    legendObrot.textContent = text;
                }

                // ====== LICZBA ZAMÓWIEŃ (2 słupki) ======
                if (chartZam) chartZam.destroy();

                var labelsZam = mergeLabels(zam1.labels, zam2.labels);
                var zamData1 = labelsZam.map(function (l) {
                    var idx = (zam1.labels || []).indexOf(l);
                    return idx >= 0 ? zam1.values[idx] : 0;
                });
                var zamData2 = labelsZam.map(function (l) {
                    var idx = (zam2.labels || []).indexOf(l);
                    return idx >= 0 ? zam2.values[idx] : 0;
                });

                var zamDatasets = [{
                    label:
                        (period === 'year'  ? 'Zamówienia (miesięcznie) – ' :
                            period === 'month' ? 'Zamówienia (tygodniowo) – '  :
                                period === 'week'  ? 'Zamówienia (dziennie) – '    :
                                    'Zamówienia (szt.) – ') + getCategoryName(cat1),
                    data: zamData1,
                    borderWidth: 1,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)'
                }];

                if (cat2 && zam2.labels.length) {
                    zamDatasets.push({
                        label:
                            (period === 'year'  ? 'Zamówienia (miesięcznie) – ' :
                                period === 'month' ? 'Zamówienia (tygodniowo) – '  :
                                    period === 'week'  ? 'Zamówienia (dziennie) – '    :
                                        'Zamówienia (szt.) – ') + getCategoryName(cat2),
                        data: zamData2,
                        borderWidth: 1,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)'
                    });
                }

                chartZam = new Chart(chartZamCtx, {
                    type: 'bar',
                    data: {
                        labels: labelsZam,
                        datasets: zamDatasets
                    },
                    options: {
                        responsive: true,
                        scales: { y: { beginAtZero: true } }
                    }
                });

                var legendZam = document.getElementById('legendZamowienia');
                if (!labelsZam.length) {
                    legendZam.textContent = 'Brak zamówień w wybranym okresie.';
                } else {
                    var z1 = labelsZam.map(function (label, i) {
                        return label + ': ' + zamData1[i] + ' szt.';
                    });
                    var txtZ = 'Seria ' + getCategoryName(cat1) + ': ' + z1.join(' | ');
                    if (cat2 && zam2.labels.length) {
                        var z2 = labelsZam.map(function (label, i) {
                            return label + ': ' + zamData2[i] + ' szt.';
                        });
                        txtZ += ' || Seria ' + getCategoryName(cat2) + ': ' + z2.join(' | ');
                    }
                    legendZam.textContent = txtZ;
                }

                // ====== DOCHÓD (2 słupki) ======
                if (chartDoch) chartDoch.destroy();

                var labelsDoch = mergeLabels(doch1.labels, doch2.labels);
                var dochData1 = labelsDoch.map(function (l) {
                    var idx = (doch1.labels || []).indexOf(l);
                    return idx >= 0 ? doch1.values[idx] : 0;
                });
                var dochData2 = labelsDoch.map(function (l) {
                    var idx = (doch2.labels || []).indexOf(l);
                    return idx >= 0 ? doch2.values[idx] : 0;
                });

                var dochDatasets = [{
                    label:
                        (period === 'year'  ? 'Dochód miesięczny (brutto) – ' :
                            period === 'month' ? 'Dochód tygodniowy (brutto) – ' :
                                period === 'week'  ? 'Dochód dzienny (brutto) – '    :
                                    'Dochód (suma brutto) – ') + getCategoryName(cat1),
                    data: dochData1,
                    borderWidth: 1,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)'
                }];

                if (cat2 && doch2.labels.length) {
                    dochDatasets.push({
                        label:
                            (period === 'year'  ? 'Dochód miesięczny (brutto) – ' :
                                period === 'month' ? 'Dochód tygodniowy (brutto) – ' :
                                    period === 'week'  ? 'Dochód dzienny (brutto) – '    :
                                        'Dochód (suma brutto) – ') + getCategoryName(cat2),
                        data: dochData2,
                        borderWidth: 1,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)'
                    });
                }

                chartDoch = new Chart(chartDochCtx, {
                    type: 'bar',
                    data: {
                        labels: labelsDoch,
                        datasets: dochDatasets
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function (value) {
                                        return value.toLocaleString('pl-PL') + ' zł';
                                    }
                                }
                            }
                        }
                    }
                });

                var legendDoch = document.getElementById('legendDochody');
                if (!labelsDoch.length) {
                    legendDoch.textContent = 'Brak danych o dochodzie w wybranym okresie.';
                } else {
                    var d1 = labelsDoch.map(function (label, i) {
                        return label + ': ' + formatPln(dochData1[i]);
                    });
                    var txtD = 'Seria ' + getCategoryName(cat1) + ': ' + d1.join(' | ');
                    if (cat2 && doch2.labels.length) {
                        var d2 = labelsDoch.map(function (label, i) {
                            return label + ': ' + formatPln(dochData2[i]);
                        });
                        txtD += ' || Seria ' + getCategoryName(cat2) + ': ' + d2.join(' | ');
                    }
                    legendDoch.textContent = txtD;
                }

                // ====== TOP PRODUKTY (2 serie) ======
                if (chartTop) chartTop.destroy();

                var labelsTop = mergeLabels(top1.labels, top2.labels);
                var topData1 = labelsTop.map(function (l) {
                    var idx = (top1.labels || []).indexOf(l);
                    return idx >= 0 ? top1.values[idx] : 0;
                });
                var topData2 = labelsTop.map(function (l) {
                    var idx = (top2.labels || []).indexOf(l);
                    return idx >= 0 ? top2.values[idx] : 0;
                });

                var labelBase = (rankBy === 'qty') ? 'Ilość' : 'Wartość brutto';

                var topDatasets = [{
                    label: labelBase + ' – ' + getCategoryName(cat1),
                    data: topData1,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)'
                }];

                if (cat2 && top2.labels.length) {
                    topDatasets.push({
                        label: labelBase + ' – ' + getCategoryName(cat2),
                        data: topData2,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)'
                    });
                }

                chartTop = new Chart(chartTopCtx, {
                    type: 'bar',
                    data: {
                        labels: labelsTop,
                        datasets: topDatasets
                    },
                    options: {
                        responsive: true,
                        indexAxis: 'y',
                        scales: { x: { beginAtZero: true } }
                    }
                });
            });
        }

        // Zastosuj filtry
        document.getElementById('applyBtn').addEventListener('click', function (e) {
            e.preventDefault();
            loadCharts();
        });

        // reaguj na zmianę "Forma wykresów"
        f.rankBy.addEventListener('change', function () {
            toggleCardsForRankBy();
            loadCharts();
        });

        // reaguj na zmianę kategorii
        if (f.category) {
            f.category.addEventListener('change', loadCharts);
        }
        if (f.category2) {
            f.category2.addEventListener('change', loadCharts);
        }

        // Pierwsze renderowanie
        toggleCardsForRankBy();
        loadCharts();
    });
</script>
@endsection
