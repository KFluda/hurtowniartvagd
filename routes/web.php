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


Route::get('/konto', [FrontendController::class, 'konto'])
    ->middleware('auth')
    ->name('konto');

Route::get('/konto/zamowienia/{id}', [FrontendController::class, 'mojeZamowienie'])
    ->middleware('auth')
    ->whereNumber('id')
    ->name('konto.zamowienie');

Route::get('/platnosc-blik', [FrontendController::class, 'blikForm'])->name('platnosc.blik');
Route::post('/platnosc-blik', [FrontendController::class, 'blikPay'])->name('platnosc.blik.pay');

/* ===================== PUBLICZNE ===================== */
Route::get('/koszyk', [FrontendController::class, 'cart'])->name('koszyk');
// SKLEP (nazwa: sklep – używana w layoutach)
Route::get('/sklep', [FrontendController::class, 'index'])->name('sklep');
Route::post('/koszyk/dodaj', [FrontendController::class, 'addToCart'])->name('koszyk.add');
// KOSZYK
Route::post('/koszyk/update', [FrontendController::class, 'updateCart'])->name('koszyk.update');
Route::post('/koszyk/usun', [FrontendController::class, 'removeFromCart'])->name('koszyk.remove');
/* ===================== PUBLICZNE ===================== */

// SKLEP
Route::get('/sklep', [FrontendController::class, 'index'])->name('sklep');

/* KOSZYK */
Route::get('/koszyk', [FrontendController::class, 'cart'])->name('koszyk');
Route::post('/koszyk/dodaj', [FrontendController::class, 'addToCart'])->name('koszyk.add');
Route::post('/koszyk/aktualizuj', [FrontendController::class, 'updateCart'])->name('koszyk.update');
Route::post('/koszyk/usun', [FrontendController::class, 'removeFromCart'])->name('koszyk.remove');

// O nas / Kontakt
Route::view('/o-nas', 'pages.o-nas')->name('pages.o-nas');
Route::get('/kontakt', [FrontendController::class, 'contactForm'])->name('kontakt.form');
Route::post('/kontakt', [FrontendController::class, 'contactSend'])->name('kontakt.send');

// Strona główna + produkt + formularz zapytania
Route::get('/', [FrontendController::class, 'home'])->name('home');

Route::get('/produkt/{id}', [FrontendController::class, 'show'])
    ->whereNumber('id')
    ->name('produkt.show');

Route::post('/zamowienie', [FrontendController::class, 'submitOrder'])
    ->name('frontend.order');

// Logowanie / wylogowanie
Route::get('/login',  [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/rejestracja',  [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/rejestracja', [AuthController::class, 'register'])->name('register.post');

/* ===================== WSPÓLNE DLA KAŻDEGO ZALOGOWANEGO ===================== */

Route::middleware('auth')->group(function () {

    Route::get('/produkty',              [ProduktController::class, 'index'])->name('produkty.index');
    Route::middleware(['auth', 'role:ADMIN,KIEROWNIK,PRACOWNIK'])->group(function () {
        Route::get('/panel', [DashboardController::class, 'index'])->name('panel');
    });


    /* Produkty */
    Route::get('/produkty',              [ProduktController::class, 'index'])->name('produkty.index');
    Route::get('/produkty/nowy',         [ProduktController::class, 'create'])->name('produkty.create');
    Route::post('/produkty',             [ProduktController::class, 'store'])->name('produkty.store');
    Route::get('/produkty/{id}/edytuj',  [ProduktController::class, 'edit'])
        ->whereNumber('id')
        ->name('produkty.edit');
    Route::put('/produkty/{id}',         [ProduktController::class, 'update'])
        ->whereNumber('id')
        ->name('produkty.update');
    Route::delete('/produkty/{id}',      [ProduktController::class, 'destroy'])
        ->whereNumber('id')
        ->name('produkty.destroy');

    /* Klienci */
    Route::get('/klienci',              [KlientController::class, 'index'])->name('klienci.index');
    Route::get('/klienci/nowy',         [KlientController::class, 'create'])->name('klienci.create');
    Route::post('/klienci',             [KlientController::class, 'store'])->name('klienci.store');
    Route::get('/klienci/{id}/edytuj',  [KlientController::class, 'edit'])
        ->whereNumber('id')
        ->name('klienci.edit');
    Route::put('/klienci/{id}',         [KlientController::class, 'update'])
        ->whereNumber('id')
        ->name('klienci.update');
    Route::delete('/klienci/{id}',      [KlientController::class, 'destroy'])
        ->whereNumber('id')
        ->name('klienci.destroy');

    /* Dostawcy */
    Route::get('/dostawcy',                 [DostawcaController::class, 'index'])->name('dostawcy.index');
    Route::get('/dostawcy/nowy',            [DostawcaController::class, 'create'])->name('dostawcy.create');
    Route::post('/dostawcy',                [DostawcaController::class, 'store'])->name('dostawcy.store');
    Route::get('/dostawcy/{id}/edytuj',     [DostawcaController::class, 'edit'])
        ->whereNumber('id')
        ->name('dostawcy.edit');
    Route::put('/dostawcy/{id}',            [DostawcaController::class, 'update'])
        ->whereNumber('id')
        ->name('dostawcy.update');
    Route::delete('/dostawcy/{id}',         [DostawcaController::class, 'destroy'])
        ->whereNumber('id')
        ->name('dostawcy.destroy');

    /* Magazyn */
    Route::get('/magazyn/stany', [MagazynController::class, 'stany'])->name('magazyn.stany');

    /* Ustawienia (placeholder) */
    Route::view('/ustawienia', 'placeholder')->name('ustawienia.index');

    /* Zamówienia – LISTA + PODGLĄD + ZMIANA STATUSU (wszyscy zalogowani, także PRACOWNIK) */
    Route::get('/zamowienia', [ZamowienieController::class, 'index'])->name('zamowienia.index');

    Route::get('/zamowienia/{id}', [ZamowienieController::class, 'show'])
        ->whereNumber('id')
        ->name('zamowienia.show');

    Route::patch('/zamowienia/{id}/status', [ZamowienieController::class, 'updateStatus'])
        ->whereNumber('id')
        ->name('zamowienia.status');
});


/* ===================== ADMIN + KIEROWNIK ===================== */

Route::middleware(['auth', 'role:ADMIN,KIEROWNIK'])->group(function () {

    /* Raporty + API do wykresów */
    Route::get('/raporty', [ReportsController::class, 'index'])->name('raporty.index');
    Route::get('/raporty/data/obrot',        [ReportsController::class, 'obrotData'])->name('raporty.data.obrot');
    Route::get('/raporty/data/zamowienia',   [ReportsController::class, 'zamowieniaData'])->name('raporty.data.zamowienia');
    Route::get('/raporty/data/top-produkty', [ReportsController::class, 'topProduktyData'])->name('raporty.data.topProdukty');

    /* Faktury sprzedaży */
    Route::get('/faktury',          [FakturaController::class, 'index'])->name('faktury.index');
    Route::get('/faktury/{id}/pdf', [FakturaController::class, 'pdf'])
        ->whereNumber('id')
        ->name('faktury.pdf');

    /* Tworzenie / zapis / anulowanie zamówień */
    Route::get('/zamowienia/nowe',  [ZamowienieController::class, 'create'])->name('zamowienia.create');
    Route::post('/zamowienia',      [ZamowienieController::class, 'store'])->name('zamowienia.store');

    Route::delete('/zamowienia/{id}', [ZamowienieController::class, 'cancel'])
        ->whereNumber('id')
        ->name('zamowienia.cancel');

    /* Stare przekierowania */
    Route::get('/zakupy', fn () => redirect()->route('zamowienia.create'))->name('zakupy.index');
    Route::get('/ruchy',  fn () => redirect()->route('zamowienia.index'))->name('ruchy.index');
});


/* ===================== ADMIN – użytkownicy ===================== */

Route::middleware(['auth', 'role:ADMIN'])->group(function () {
    Route::get   ('/uzytkownicy',             [UsersController::class,'index'])->name('uzytkownicy.index');
    Route::get   ('/uzytkownicy/nowy',        [UsersController::class,'create'])->name('uzytkownicy.create');
    Route::post  ('/uzytkownicy',             [UsersController::class,'store'])->name('uzytkownicy.store');
    Route::get   ('/uzytkownicy/{id}/edytuj', [UsersController::class,'edit'])
        ->whereNumber('id')
        ->name('uzytkownicy.edit');
    Route::put   ('/uzytkownicy/{id}',        [UsersController::class,'update'])
        ->whereNumber('id')
        ->name('uzytkownicy.update');
    Route::delete('/uzytkownicy/{id}',        [UsersController::class,'destroy'])
        ->whereNumber('id')
        ->name('uzytkownicy.destroy');

    Route::patch('/uzytkownicy/{id}/toggle', [UsersController::class, 'toggleActive'])
        ->whereNumber('id')
        ->name('uzytkownicy.toggle');

    Route::post('/uzytkownicy/{id}/reset-pass', [UsersController::class, 'resetPassword'])
        ->whereNumber('id')
        ->name('uzytkownicy.resetPass');
});
