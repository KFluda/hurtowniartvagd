@extends('layouts.app')
@section('title','Klienci')

@section('content')
<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Klienci</h1>

        <div class="d-flex gap-2">
            <form method="get" class="d-flex gap-2">
                <input class="form-control" type="search" name="q" value="{{ $q }}" placeholder="Szukaj: nazwa / NIP / miasto">
                <button class="btn btn-primary">Szukaj</button>
            </form>
            <a class="btn btn-success" href="{{ route('klienci.create') }}">Dodaj klienta</a>
        </div>
    </div>

    @if(session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

    <div class="table-responsive bg-white border rounded-3">
        <table class="table table-sm mb-0 align-middle">
            <thead>
            <tr>
                <th>Nazwa</th>
                <th style="width:10rem;">NIP</th>
                <th>Adres</th>
                <th>Miasto</th>
                <th>E-mail</th>
                <th>Telefon</th>
                <th style="width:6rem;">Status</th>
                <th style="width:10rem;">Akcje</th>
            </tr>
            </thead>
            <tbody>
            @forelse($klienci as $k)
            <tr>
                <td class="fw-semibold">{{ $k->nazwa }}</td>
                <td>{{ $k->nip }}</td>
                <td>{{ $k->ulica }}</td>
                <td>{{ $k->miasto }}</td>
                <td>{{ $k->email }}</td>
                <td>{{ $k->telefon }}</td>
                <td>{{ !empty($k->aktywny) ? 'aktyw.' : 'nieaktyw.' }}</td>
                <td class="d-flex gap-2">
                    <a href="{{ route('klienci.edit', $k->id_klienta) }}" class="btn btn-sm btn-outline-primary">Edytuj</a>
                    <form method="post" action="{{ route('klienci.destroy', $k->id_klienta) }}">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Usuń</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center text-muted">Brak danych.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $klienci->links() }}</div>
</div>
@endsection

