<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProduktController;
use App\Http\Controllers\MagazynController;
use App\Http\Controllers\FakturaController;
use App\Http\Controllers\DostawcaController;
use App\Http\Controllers\KlientController;
use App\Http\Controllers\ZamowienieController;
use App\Http\Controllers\ReportsController;

Route::middleware('auth')->group(function () {

    // Widok raportów (z filtrami i płótnami)
    Route::get('/raporty', [ReportsController::class, 'index'])->name('raporty.index');

    // Dane do wykresów (AJAX)
    Route::get('/raporty/data/obrot',        [ReportsController::class, 'obrotData'])->name('raporty.data.obrot');
    Route::get('/raporty/data/zamowienia',   [ReportsController::class, 'zamowieniaData'])->name('raporty.data.zamowienia');
    Route::get('/raporty/data/top-produkty', [ReportsController::class, 'topProduktyData'])->name('raporty.data.topProdukty');
});
// Zmiana statusu (bez anulowania)
Route::patch('/zamowienia/{id}/status', [ZamowienieController::class, 'updateStatus'])
    ->name('zamowienia.status');

// Anulowanie zamówienia (przywraca stany)
Route::delete('/zamowienia/{id}', [ZamowienieController::class, 'cancel'])
    ->name('zamowienia.cancel');

// Lista zamówień do zafakturowania
Route::get('/faktury', [FakturaController::class, 'index'])->name('faktury.index');

// Generowanie PDF z konkretnego zamówienia
Route::get('/faktury/{id}/pdf', [FakturaController::class, 'pdf'])->name('faktury.pdf');

/* Public */
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/login',  [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/* Auth-only */
Route::middleware('auth')->group(function () {

    /* Panel */
    Route::get('/panel', [DashboardController::class, 'index'])->name('panel');

    /* Produkty */
    Route::get('/produkty',              [ProduktController::class, 'index'])->name('produkty.index');
    Route::get('/produkty/nowy',         [ProduktController::class, 'create'])->name('produkty.create');
    Route::post('/produkty',             [ProduktController::class, 'store'])->name('produkty.store');
    Route::get('/produkty/{id}/edytuj',  [ProduktController::class, 'edit'])->name('produkty.edit');
    Route::put('/produkty/{id}',         [ProduktController::class, 'update'])->name('produkty.update');
    Route::delete('/produkty/{id}',      [ProduktController::class, 'destroy'])->name('produkty.destroy');

    /* Klienci */
    Route::get('/klienci',              [KlientController::class, 'index'])->name('klienci.index');
    Route::get('/klienci/nowy',         [KlientController::class, 'create'])->name('klienci.create');
    Route::post('/klienci',             [KlientController::class, 'store'])->name('klienci.store');
    Route::get('/klienci/{id}/edytuj',  [KlientController::class, 'edit'])->name('klienci.edit');
    Route::put('/klienci/{id}',         [KlientController::class, 'update'])->name('klienci.update');
    Route::delete('/klienci/{id}',      [KlientController::class, 'destroy'])->name('klienci.destroy');

    /* Dostawcy */
    Route::get('/dostawcy',                 [DostawcaController::class, 'index'])->name('dostawcy.index');
    Route::get('/dostawcy/nowy',            [DostawcaController::class, 'create'])->name('dostawcy.create');
    Route::post('/dostawcy',                [DostawcaController::class, 'store'])->name('dostawcy.store');
    Route::get('/dostawcy/{id}/edytuj',     [DostawcaController::class, 'edit'])->name('dostawcy.edit');
    Route::put('/dostawcy/{id}',            [DostawcaController::class, 'update'])->name('dostawcy.update');
    Route::delete('/dostawcy/{id}',         [DostawcaController::class, 'destroy'])->name('dostawcy.destroy');

    /* Zamówienia – NAJWAŻNIEJSZE */
    Route::get('/zamowienia',       [ZamowienieController::class, 'index'])->name('zamowienia.index');
    Route::get('/zamowienia/nowe',  [ZamowienieController::class, 'create'])->name('zamowienia.create');
    Route::post('/zamowienia',      [ZamowienieController::class, 'store'])->name('zamowienia.store');
    Route::get('/zamowienia/{id}',  [ZamowienieController::class, 'show'])->name('zamowienia.show');

    /* Stary link "Zakupy" -> teraz "Stwórz zamówienie" */
    Route::get('/zakupy', fn () => redirect()->route('zamowienia.create'))->name('zakupy.index');

    /* Stary link "Ruchy" -> teraz "Zamówienia (lista)" */
    Route::get('/ruchy', fn () => redirect()->route('zamowienia.index'))->name('ruchy.index');

    /* Magazyn + placeholdery sekcji, które jeszcze budujesz */
    Route::get('/magazyn/stany', [MagazynController::class, 'stany'])->name('magazyn.stany');

    Route::view('/ustawienia', 'placeholder')->name('ustawienia.index');

    /* Strony statyczne */
    Route::view('/o-nas',   'pages.o-nas')->name('pages.o-nas');
    Route::view('/kontakt', 'pages.kontakt')->name('pages.kontakt');
});
