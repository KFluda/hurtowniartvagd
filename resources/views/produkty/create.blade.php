@extends('layouts.app')
@section('title', 'Dodaj produkt')

@section('content')
<div class="container py-3">
    <h1 class="h4 mb-3">Dodaj produkt</h1>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="post"
          action="{{ route('produkty.store') }}"
          enctype="multipart/form-data"
          class="bg-white border rounded-3 p-3">
        @csrf

        @php
        // przy create nie ma jeszcze $produkt, więc tworzymy pusty obiekt
        $produkt = $produkt ?? null;
        @endphp

        @include('produkty.form', [
        'produkt'    => $produkt,
        'producenci' => $producenci,
        'kategorie'  => $kategorie,
        ])

        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary">Dodaj</button>
            <a href="{{ route('produkty.index') }}" class="btn btn-outline-secondary">Anuluj</a>
        </div>
    </form>
</div>
@endsection

