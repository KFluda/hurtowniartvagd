<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProduktController;
use App\Http\Controllers\MagazynController;
use App\Http\Controllers\DashboardController;

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
    Route::view('/klienci', 'placeholder')->name('klienci.index');
    Route::view('/dostawcy', 'placeholder')->name('dostawcy.index');
    Route::view('/ruchy', 'placeholder')->name('ruchy.index');
    Route::view('/raporty', 'placeholder')->name('raporty.index');
    Route::view('/ustawienia', 'placeholder')->name('ustawienia.index');
    Route::view('/o-nas', 'pages.o-nas')->name('pages.o-nas');
    Route::view('/kontakt', 'pages.kontakt')->name('pages.kontakt');
});
