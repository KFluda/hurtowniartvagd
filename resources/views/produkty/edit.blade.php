@extends('layouts.app')
@section('title', 'Edytuj produkt')

@section('content')
<div class="container py-4">
    <h1 class="h4 mb-3">Edytuj produkt</h1>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
    @endif

    <form method="post" action="{{ route('produkty.update', $produkt->id_produktu) }}" class="bg-white border rounded-3 p-3">
        @csrf
        @method('PUT')

        {{-- wspólny formularz pól --}}
        @include('produkty.form', ['mode' => 'edit'])

        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary">Zapisz</button>
            <a href="{{ route('produkty.index') }}" class="btn btn-outline-secondary">Anuluj</a>
        </div>
    </form>
</div>
@endsection
