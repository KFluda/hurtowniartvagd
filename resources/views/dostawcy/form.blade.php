@extends('layouts.app')
@section('title', $mode === 'create' ? 'Dodaj dostawcę' : 'Edytuj dostawcę')

@section('content')
<div class="container py-3">
    <h1 class="h4 mb-3">{{ $mode === 'create' ? 'Dodaj dostawcę' : 'Edytuj dostawcę' }}</h1>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
    </div>
    @endif

    <form method="post"
          action="{{ $mode === 'create'
                  ? route('dostawcy.store')
                  : route('dostawcy.update', $id) }}"
          class="bg-white border rounded-3 p-3">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nazwa</label>
                <input name="nazwa" class="form-control" value="{{ old('nazwa',$dostawca->nazwa) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">NIP</label>
                <input name="nip" class="form-control" value="{{ old('nip',$dostawca->nip) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="aktywny" class="form-select">
                    <option value="1" @selected(old('aktywny',$dostawca->aktywny)==1)>aktywny</option>
                    <option value="0" @selected(old('aktywny',$dostawca->aktywny)==0)>nieaktywny</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Ulica i nr</label>
                <input name="ulica" class="form-control" value="{{ old('ulica',$dostawca->ulica) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Kod pocztowy</label>
                <input name="kod_pocztowy" class="form-control" value="{{ old('kod_pocztowy',$dostawca->kod_pocztowy) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Miasto</label>
                <input name="miasto" class="form-control" value="{{ old('miasto',$dostawca->miasto) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Kraj</label>
                <input name="kraj" class="form-control" value="{{ old('kraj',$dostawca->kraj) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">E-mail</label>
                <input name="email" class="form-control" value="{{ old('email',$dostawca->email) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Telefon</label>
                <input name="telefon" class="form-control" value="{{ old('telefon',$dostawca->telefon) }}">
            </div>
        </div>

        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary">{{ $mode === 'create' ? 'Dodaj' : 'Zapisz' }}</button>
            <a href="{{ route('dostawcy.index') }}" class="btn btn-outline-secondary">Anuluj</a>
        </div>
    </form>
</div>
@endsection

