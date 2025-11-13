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
use App\Http\Controllers\UsersController;
use App\Http\Controllers\FrontendController;
// Publiczne strony
Route::view('/o-nas', 'pages.o-nas')->name('pages.o-nas');
Route::view('/kontakt', 'pages.kontakt')->name('pages.kontakt');

Route::get('/', [FrontendController::class, 'home'])->name('home');
Route::get('/produkt/{id}', [FrontendController::class, 'show'])->name('produkt.show');
Route::post('/zamowienie', [FrontendController::class, 'submitOrder'])->name('frontend.order');
Route::get('/sklep', [FrontendController::class, 'index'])->name('sklep');

Route::get('/login',  [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/* ===================== Panel + wspólne dla wszystkich zalogowanych ===================== */
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

    /* Magazyn */
    Route::get('/magazyn/stany', [MagazynController::class, 'stany'])->name('magazyn.stany');

    /* Strony statyczne / placeholdery */
    Route::view('/ustawienia', 'placeholder')->name('ustawienia.index');

    /* Zamówienia – dostępne DLA KAŻDEGO zalogowanego (również PRACOWNIK) */
    Route::get('/zamowienia',      [ZamowienieController::class, 'index'])->name('zamowienia.index');
    Route::get('/zamowienia/{id}', [ZamowienieController::class, 'show'])->name('zamowienia.show');

    // Zmiana statusu — np. PRACOWNIK też może
    Route::patch('/zamowienia/{id}/status', [ZamowienieController::class, 'updateStatus'])->name('zamowienia.status');
});

/* ===================== ADMIN + KIEROWNIK ===================== */
/* Raporty, Faktury, tworzenie/anulowanie zamówień */
Route::middleware(['auth', 'role:ADMIN,KIEROWNIK'])->group(function () {

    /* Raporty + API do wykresów */
    Route::get('/raporty', [ReportsController::class, 'index'])->name('raporty.index');
    Route::get('/raporty/data/obrot',        [ReportsController::class, 'obrotData'])->name('raporty.data.obrot');
    Route::get('/raporty/data/zamowienia',   [ReportsController::class, 'zamowieniaData'])->name('raporty.data.zamowienia');
    Route::get('/raporty/data/top-produkty', [ReportsController::class, 'topProduktyData'])->name('raporty.data.topProdukty');

    /* Faktury sprzedaży */
    Route::get('/faktury',        [FakturaController::class, 'index'])->name('faktury.index');
    Route::get('/faktury/{id}/pdf',[FakturaController::class, 'pdf'])->name('faktury.pdf');

    /* Tworzenie / zapis / anulowanie zamówień */
    Route::get('/zamowienia/nowe',  [ZamowienieController::class, 'create'])->name('zamowienia.create');
    Route::post('/zamowienia',      [ZamowienieController::class, 'store'])->name('zamowienia.store');
    Route::delete('/zamowienia/{id}', [ZamowienieController::class, 'cancel'])->name('zamowienia.cancel');

    /* Stare przekierowania */
    Route::get('/zakupy', fn () => redirect()->route('zamowienia.create'))->name('zakupy.index');
    Route::get('/ruchy',  fn () => redirect()->route('zamowienia.index'))->name('ruchy.index');
});

/* ===================== ADMIN – zarządzanie użytkownikami ===================== */
Route::middleware(['auth', 'role:ADMIN'])->group(function () {
    Route::get   ('/uzytkownicy',             [UsersController::class,'index'])->name('uzytkownicy.index');
    Route::get   ('/uzytkownicy/nowy',        [UsersController::class,'create'])->name('uzytkownicy.create');
    Route::post  ('/uzytkownicy',             [UsersController::class,'store'])->name('uzytkownicy.store');
    Route::get   ('/uzytkownicy/{id}/edytuj', [UsersController::class,'edit'])->name('uzytkownicy.edit');
    Route::put   ('/uzytkownicy/{id}',        [UsersController::class,'update'])->name('uzytkownicy.update');
    Route::delete('/uzytkownicy/{id}',        [UsersController::class,'destroy'])->name('uzytkownicy.destroy');

    // extra akcje admina
    Route::patch('/uzytkownicy/{id}/toggle',       [UsersController::class, 'toggleActive'])->name('uzytkownicy.toggle');
    Route::post ('/uzytkownicy/{id}/reset-pass',   [UsersController::class, 'resetPassword'])->name('uzytkownicy.resetPass');
});
