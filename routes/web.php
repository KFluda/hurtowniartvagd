<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProduktController;
use App\Http\Controllers\MagazynController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FakturaController;
use App\Http\Controllers\DostawcaController;
use App\Http\Controllers\KlientController;
Route::get('/klienci', [KlientController::class, 'index'])->name('klienci.index');
Route::get('/klienci',              [KlientController::class, 'index'])->name('klienci.index');
Route::get('/klienci/nowy',         [KlientController::class, 'create'])->name('klienci.create');
Route::post('/klienci',             [KlientController::class, 'store'])->name('klienci.store');
Route::get('/klienci/{id}/edytuj',  [KlientController::class, 'edit'])->name('klienci.edit');
Route::put('/klienci/{id}',         [KlientController::class, 'update'])->name('klienci.update');
Route::delete('/klienci/{id}',      [KlientController::class, 'destroy'])->name('klienci.destroy');


Route::get('/dostawcy',                 [DostawcaController::class, 'index'])->name('dostawcy.index');
Route::get('/dostawcy/nowy',            [DostawcaController::class, 'create'])->name('dostawcy.create');
Route::post('/dostawcy',                [DostawcaController::class, 'store'])->name('dostawcy.store');
Route::get('/dostawcy/{id}/edytuj',     [DostawcaController::class, 'edit'])->name('dostawcy.edit');
Route::put('/dostawcy/{id}',            [DostawcaController::class, 'update'])->name('dostawcy.update');
Route::delete('/dostawcy/{id}',         [DostawcaController::class, 'destroy'])->name('dostawcy.destroy');

Route::get('/faktury/nowa', [FakturaController::class, 'create'])->name('faktury.create');
Route::post('/faktury',      [FakturaController::class, 'store'])->name('faktury.store');
Route::get('/faktury/{id}',  [FakturaController::class, 'show'])->name('faktury.show');

Route::middleware('auth')->group(function () {
    Route::get('/panel', [DashboardController::class, 'index'])->name('panel');
    Route::get('/produkty', [ProduktController::class, 'index'])->name('produkty.index');
});

Route::get('/', function () {
    return view('welcome');  // strona główna
})->name('home');

Route::get('/login',  [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/produkty', [ProduktController::class, 'index'])->name('produkty.index');
    Route::get('/magazyn/stany', [MagazynController::class, 'stany'])->name('magazyn.stany');
    Route::view('/faktury', 'placeholder')->name('faktury.index');
    Route::view('/zakupy', 'placeholder')->name('zakupy.index');

    Route::get('/dostawcy', [DostawcaController::class, 'index'])->name('dostawcy.index');
    Route::view('/ruchy', 'placeholder')->name('ruchy.index');
    Route::view('/raporty', 'placeholder')->name('raporty.index');
    Route::view('/ustawienia', 'placeholder')->name('ustawienia.index');
    Route::view('/o-nas', 'pages.o-nas')->name('pages.o-nas');
    Route::view('/kontakt', 'pages.kontakt')->name('pages.kontakt');
});
