@extends('layouts.app')
@section('title','Zamówienia')

@section('content')
<div class="container py-3">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 mb-0">Zamówienia</h1>
        <a href="{{ route('zamowienia.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Nowe zamówienie
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                <tr>
                    <th>Numer zamówienia</th>
                    <th>Data złożenia</th>
                    <th>Klient</th>
                    <th class="text-end">Suma brutto</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" style="width:140px;">Akcje</th>
                </tr>
                </thead>
                <tbody>
                @forelse($zamowienia as $z)
                <tr>
                    <td class="fw-semibold">{{ $z->numer_zamowienia }}</td>
                    <td>{{ \Carbon\Carbon::parse($z->data_wystawienia)->format('d.m.Y') }}</td>
                    <td>{{ $z->klient ?? '—' }}</td>
                    <td class="text-end">{{ number_format($z->suma_brutto, 2, ',', ' ') }} zł</td>
                    <td class="text-center">
                        @php
                        $statusColor = match($z->status) {
                        'zrealizowane' => 'success',
                        'anulowane' => 'danger',
                        default => 'warning'
                        };
                        @endphp
                        <span class="badge bg-{{ $statusColor }}">{{ ucfirst($z->status) }}</span>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('zamowienia.show', $z->id_zamowienia) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> Podgląd
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Brak zamówień do wyświetlenia.</td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $zamowienia->links() }}
    </div>

</div>
@endsection
