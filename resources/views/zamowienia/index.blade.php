@extends('layouts.app')
@section('title','Zamówienia')
@section('content')
<div class="container py-4">
    <h1 class="h4">Zamówienia – DZIAŁA ✅</h1>
    <a href="{{ route('zamowienia.create') }}" class="btn btn-success mt-2">Nowe zamówienie</a>
</div>
@endsection
