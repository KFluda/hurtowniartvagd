@extends('layouts.app')
@section('title','Produkty')

@section('content')
<div class="container py-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 mb-0">Produkty</h1>

        <form method="get" class="d-flex gap-2">
            <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Szukaj: nazwa / SKU / EAN" style="min-width: 280px">
            <button class="btn btn-primary">Szukaj</button>
            <a href="{{ route('produkty.create') }}" class="btn btn-success">Dodaj produkt</a>
        </form>
    </div>

    @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="table-responsive bg-white border rounded-3">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>SKU</th>
                <th>Nazwa</th>
                <th>EAN</th>
                <th>Producent</th>
                <th>Kategoria</th>
                <th class="text-end">Ilość</th>
                <th>Status</th>
                <th>Info</th>
                <th style="width:140px">Akcje</th>
            </tr>
            </thead>
            <tbody>
            @forelse($produkty as $p)
            <tr>
                <td class="text-monospace">{{ $p->kod_sku }}</td>
                <td>{{ $p->nazwa }}</td>
                <td class="text-monospace">{{ $p->ean }}</td>
                <td>{{ $p->producent }}</td>
                <td>{{ $p->kategoria }}</td>
                <td class="text-center">
                    @php
                    $qty = (float)($p->ilosc ?? 0);
                    // progi: <=5 czerwony, 6–20 żółty, >20 zielony
                    $badge = $qty <= 5 ? 'bg-danger'
                    : ($qty <= 20 ? 'bg-warning text-dark'
                    : 'bg-success');
                    @endphp
                    <span class="badge rounded-pill {{ $badge }}">{{ (int)$qty }}</span>
                </td>

                <td>
                    @if($p->aktywny)
                    <span class="badge bg-success">aktywny</span>
                    @else
                    <span class="badge bg-secondary">nieaktywny</span>
                    @endif
                </td>

                <td class="text-center">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#infoModal{{ $p->id_produktu }}"
                       class="text-info" title="Szczegóły produktu" style="font-size: 1.4rem;">
                        <i class="bi bi-info-circle-fill"></i>
                    </a>
                </td>

                <td>
                    <div class="d-flex gap-2">
                        <a href="{{ route('produkty.edit',$p->id_produktu) }}" class="btn btn-sm btn-outline-primary">Edytuj</a>
                        <form method="post" action="{{ route('produkty.destroy',$p->id_produktu) }}" onsubmit="return confirm('Usunąć produkt?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Usuń</button>
                        </form>
                    </div>
                </td>
            </tr>

            {{-- MODAL ZE SZCZEGÓŁAMI + ZDJĘCIEM --}}
            @php
            // URL zdjęcia: z bazy (storage), a jeśli brak – placeholder
            $imageUrl = $p->image
            ? asset('storage/products/'.$p->image)
            : 'https://via.placeholder.com/600x400?text=Brak+zdj%C4%99cia';

            $cenaNetto  = $p->cena_netto ?? 0;
            $stawkaVat  = $p->stawka_vat ?? 0;
            $cenaBrutto = $cenaNetto ? $cenaNetto * (1 + $stawkaVat / 100) : 0;
            @endphp

            <div class="modal fade" id="infoModal{{ $p->id_produktu }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title">{{ $p->nazwa }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-3">
                                {{-- LEWA KOLUMNA – ZDJĘCIE --}}
                                <div class="col-md-5">
                                    <div class="border rounded-3 overflow-hidden">
                                        <img src="{{ $imageUrl }}" alt="{{ $p->nazwa }}" class="img-fluid">
                                    </div>
                                </div>

                                {{-- PRAWA KOLUMNA – DANE --}}
                                <div class="col-md-7">
                                    <ul class="list-group list-group-flush small mb-3">
                                        <li class="list-group-item"><strong>SKU:</strong> {{ $p->kod_sku }}</li>
                                        <li class="list-group-item"><strong>EAN:</strong> {{ $p->ean }}</li>
                                        <li class="list-group-item"><strong>Producent:</strong> {{ $p->producent ?? '—' }}</li>
                                        <li class="list-group-item"><strong>Kategoria:</strong> {{ $p->kategoria ?? '—' }}</li>

                                        <li class="list-group-item">
                                            <strong>Waga:</strong> {{ $p->waga_kg ?? '—' }} kg
                                        </li>
                                        <li class="list-group-item">
                                            <strong>Wymiary (S×W×G):</strong>
                                            {{ $p->szerokosc_mm ?? '—' }} × {{ $p->wysokosc_mm ?? '—' }} × {{ $p->glebokosc_mm ?? '—' }} mm
                                        </li>
                                        <li class="list-group-item">
                                            <strong>Gwarancja:</strong> {{ $p->gwarancja_miesiecy ?? '—' }} mies.
                                        </li>

                                        <li class="list-group-item">
                                            <strong>Numer seryjny:</strong>
                                            {{ $p->czy_z_numerem_seryjnym ? 'wymagany' : 'brak' }}
                                        </li>

                                        <li class="list-group-item">
                                            <strong>Stan magazynowy:</strong>
                                            @php
                                            $ilosc = (float) ($p->ilosc ?? 0);
                                            if ($ilosc <= 5) {
                                            $badge = 'danger';
                                            $status = 'Niski';
                                            } elseif ($ilosc <= 20) {
                                            $badge = 'warning';
                                            $status = 'Średni';
                                            } else {
                                            $badge = 'success';
                                            $status = 'Wysoki';
                                            }
                                            @endphp
                                            <span class="badge bg-{{ $badge }}">
                                                    {{ number_format($ilosc, 0, ',', ' ') }} szt. ({{ $status }})
                                                </span>
                                        </li>

                                        <li class="list-group-item">
                                            <strong>Status:</strong>
                                            {{ $p->aktywny ? 'aktywny' : 'nieaktywny' }}
                                        </li>

                                        <li class="list-group-item">
                                            <strong>Cena netto:</strong>
                                            {{ $cenaNetto ? number_format($cenaNetto, 2, ',', ' ') . ' zł' : '—' }}<br>
                                            <strong>Stawka VAT:</strong>
                                            {{ $stawkaVat ? $stawkaVat.' %' : '—' }}<br>
                                            <strong>Cena brutto:</strong>
                                            {{ $cenaNetto ? number_format($cenaBrutto, 2, ',', ' ') . ' zł' : '—' }}
                                        </li>
                                    </ul>

                                    <a href="{{ route('produkty.edit',$p->id_produktu) }}" class="btn btn-sm btn-outline-primary">
                                        Przejdź do edycji
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            @empty
            <tr>
                <td colspan="9" class="text-center text-muted py-4">Brak danych.</td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $produkty->links() }}
    </div>
</div>
@endsection
