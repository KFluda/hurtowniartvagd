@extends('layouts.app')
@section('title', $mode==='create' ? 'Nowy użytkownik' : 'Edytuj użytkownika')

@section('content')
<div class="container py-3">
    <h1 class="h4 mb-3">{{ $mode==='create' ? 'Nowy użytkownik' : 'Edytuj użytkownika' }}</h1>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="post"
          action="{{ $mode==='create' ? route('uzytkownicy.store') : route('uzytkownicy.update',$id) }}"
          class="bg-white border rounded-3 p-3">
        @csrf
        @if($mode==='edit') @method('PUT') @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Email (login)</label>
                <input name="email" type="email" class="form-control"
                       value="{{ old('email',$user->email) }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Imię i nazwisko</label>
                <input name="imie_nazwisko" class="form-control"
                       value="{{ old('imie_nazwisko',$user->imie_nazwisko) }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Rola</label>
                <select name="rola" class="form-select" required>
                    @foreach($roles as $key => $label)
                    <option value="{{ $key }}" @selected(old('rola',$user->rola)===$key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Aktywny</label>
                <select name="aktywny" class="form-select">
                    <option value="1" @selected(old('aktywny',$user->aktywny)==1)>tak</option>
                    <option value="0" @selected(old('aktywny',$user->aktywny)==0)>nie</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">
                    Hasło
                    @if($mode==='edit')
                    <span class="text-muted small">(pozostaw puste, aby nie zmieniać)</span>
                    @endif
                </label>
                <input name="haslo" type="password" class="form-control" {{ $mode==='create' ? 'required' : '' }}>
            </div>
        </div>

        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary">{{ $mode==='create' ? 'Dodaj' : 'Zapisz' }}</button>
            <a href="{{ route('uzytkownicy.index') }}" class="btn btn-outline-secondary">Anuluj</a>
        </div>
    </form>
</div>
@endsection
