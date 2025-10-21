<?php

use App\Http\Controllers\ProduktController;
use Illuminate\Support\Facades\Route;

Route::get('/', function (){
    return redirect()->route('produkty.index');
});
Route::get('/produkty', [ProduktController::class, 'index'])->name('produkty.index');
use App\Http\Controllers\MagazynController;
Route::get('/magazyn/stany', [MagazynController::class, 'stany'])->name('magazyn.stany');
