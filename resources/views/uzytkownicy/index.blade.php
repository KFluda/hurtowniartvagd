@extends('layouts.app')
@section('title','Użytkownicy')

@section('content')
<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Użytkownicy</h1>
        <form method="get" class="d-flex gap-2">
            <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Szukaj: email / imię i nazwisko / rola" style="min-width: 320px">
            <button class="btn btn-primary">Szukaj</button>
            <a href="{{ route('uzytkownicy.create') }}" class="btn btn-success">Nowy użytkownik</a>
        </form>
    </div>

    @if(session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif
    @if(session('error'))  <div class="alert alert-danger">{{ session('error') }}</div> @endif

    <div class="table-responsive bg-white border rounded-3">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Email</th>
                <th>Imię i nazwisko</th>
                <th>Rola</th>
                <th>Aktywny</th>
                <th style="width:160px">Akcje</th>
            </tr>
            </thead>
            <tbody>
            @forelse($users as $u)
            @php
            $roleBadge = match(strtoupper($u->rola)) {
            'ADMIN'      => 'danger',
            'HANDLOWIEC' => 'primary',
            default      => 'secondary',
            };
            @endphp
            <tr>
                <td class="text-monospace">{{ $u->email }}</td>
                <td>{{ $u->imie_nazwisko }}</td>
                <td><span class="badge bg-{{ $roleBadge }}">{{ $u->rola }}</span></td>
                <td>
                    @if($u->aktywny)
                    <span class="badge bg-success">tak</span>
                    @else
                    <span class="badge bg-secondary">nie</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex gap-2">
                        <a href="{{ route('uzytkownicy.edit',$u->id_uzytkownika) }}" class="btn btn-sm btn-outline-primary">Edytuj</a>
                        <form method="post" action="{{ route('uzytkownicy.destroy',$u->id_uzytkownika) }}" onsubmit="return confirm('Usunąć użytkownika?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Usuń</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted py-4">Brak danych.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $users->links() }}
    </div>
</div>
@endsection
