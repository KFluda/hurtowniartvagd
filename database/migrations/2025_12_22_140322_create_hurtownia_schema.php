<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Słowniki / podstawowe
        Schema::create('kategorie', function (Blueprint $table) {
            $table->id('id_kategorii');
            $table->string('nazwa', 255);
            $table->timestamps();
        });

        Schema::create('producenci', function (Blueprint $table) {
            $table->id('id_producenta');
            $table->string('nazwa', 255);
            $table->timestamps();
        });

        // 2) Produkty
        Schema::create('produkty', function (Blueprint $table) {
            $table->id('id_produktu');
            $table->foreignId('id_kategorii')->constrained('kategorie', 'id_kategorii');
            $table->foreignId('id_producenta')->nullable()->constrained('producenci', 'id_producenta');

            $table->string('nazwa', 255);
            $table->text('opis')->nullable();

            $table->decimal('cena_netto', 10, 2)->default(0);
            $table->string('image')->nullable(); // ścieżka/nazwa pliku

            // jeśli gdzieś w kodzie sortujesz po dacie utworzenia jako data_utworzenia:
            $table->timestamp('data_utworzenia')->nullable();

            $table->timestamps();
        });

        Schema::create('ceny_produktow', function (Blueprint $table) {
            $table->id('id_ceny');
            $table->foreignId('id_produktu')->constrained('produkty', 'id_produktu');
            $table->decimal('cena_netto', 10, 2);
            $table->timestamp('od')->nullable();
            $table->timestamps();
        });

        // 3) Magazyn
        Schema::create('magazyny', function (Blueprint $table) {
            $table->id('id_magazynu');
            $table->string('nazwa', 255);
            $table->string('adres')->nullable();
            $table->timestamps();
        });

        Schema::create('lokalizacje', function (Blueprint $table) {
            $table->id('id_lokalizacji');
            $table->foreignId('id_magazynu')->constrained('magazyny', 'id_magazynu');
            $table->string('kod', 100); // np. A1-01
            $table->string('opis')->nullable();
            $table->timestamps();
        });

        // stan w magazynie dla produktu
        Schema::create('stany_magazynowe', function (Blueprint $table) {
            $table->id('id_stanu');
            $table->foreignId('id_magazynu')->constrained('magazyny', 'id_magazynu');
            $table->foreignId('id_produktu')->constrained('produkty', 'id_produktu');
            $table->integer('stan')->default(0);
            $table->timestamps();

            $table->unique(['id_magazynu', 'id_produktu'], 'uniq_mag_produkt');
        });

        Schema::create('ruchy_magazynowe', function (Blueprint $table) {
            $table->id('id_ruchu');
            $table->foreignId('id_magazynu')->constrained('magazyny', 'id_magazynu');
            $table->foreignId('id_produktu')->constrained('produkty', 'id_produktu');
            $table->enum('typ', ['PRZYJECIE','WYDAJ','KOREKTA']);
            $table->integer('ilosc');
            $table->string('opis')->nullable();
            $table->timestamps();
        });

        // 4) Kontrahenci / dostawcy / klienci (proste wersje)
        Schema::create('kontrahenci', function (Blueprint $table) {
            $table->id('id_kontrahenta');
            $table->string('nazwa', 255);
            $table->string('nip', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('telefon')->nullable();
            $table->string('adres')->nullable();
            $table->timestamps();
        });

        Schema::create('dostawcy', function (Blueprint $table) {
            $table->id('id_dostawcy');
            $table->string('nazwa', 255);
            $table->string('email')->nullable();
            $table->string('telefon')->nullable();
            $table->string('adres')->nullable();
            $table->timestamps();
        });

        Schema::create('klienci', function (Blueprint $table) {
            $table->id('id_klienta');
            $table->string('imie', 100);
            $table->string('nazwisko', 100);
            $table->string('email')->nullable();
            $table->string('telefon')->nullable();
            $table->string('adres')->nullable();
            $table->timestamps();
        });

        // 5) Zamówienia
        Schema::create('zamowienia', function (Blueprint $table) {
            $table->id('id_zamowienia');

            // jeśli w sklepie konto jest na Laravel users:
            $table->foreignId('user_id')->nullable()->constrained('users');

            // jeśli operujesz na tabeli klienci:
            $table->foreignId('id_klienta')->nullable()->constrained('klienci', 'id_klienta');

            $table->enum('status', ['NOWE','W_REALIZACJI','ZREALIZOWANE','ANULOWANE'])->default('NOWE');
            $table->decimal('suma', 10, 2)->default(0);
            $table->string('metoda_platnosci')->nullable(); // np. BLIK
            $table->timestamps();
        });

        Schema::create('zamowienia_pozycje', function (Blueprint $table) {
            $table->id('id_pozycji');
            $table->foreignId('id_zamowienia')->constrained('zamowienia', 'id_zamowienia')->onDelete('cascade');
            $table->foreignId('id_produktu')->constrained('produkty', 'id_produktu');
            $table->integer('ilosc');
            $table->decimal('cena_netto', 10, 2)->default(0);
            $table->timestamps();
        });

        // 6) Faktury
        Schema::create('faktury_sprzedazy', function (Blueprint $table) {
            $table->id('id_faktury');
            $table->foreignId('id_zamowienia')->nullable()->constrained('zamowienia', 'id_zamowienia')->nullOnDelete();
            $table->string('numer', 100)->unique();
            $table->date('data_wystawienia')->nullable();
            $table->string('nabywca_nazwa')->nullable();
            $table->string('nabywca_nip', 20)->nullable();
            $table->string('nabywca_adres')->nullable();
            $table->timestamps();
        });

        Schema::create('faktury_pozycje', function (Blueprint $table) {
            $table->id('id_pozycji_faktury');
            $table->foreignId('id_faktury')->constrained('faktury_sprzedazy', 'id_faktury')->onDelete('cascade');
            $table->foreignId('id_produktu')->constrained('produkty', 'id_produktu');
            $table->integer('ilosc');
            $table->decimal('cena_netto', 10, 2)->default(0);
            $table->timestamps();
        });

        // 7) Użytkownicy (pracownicy) – jeśli faktycznie używasz tej tabeli w kodzie
        Schema::create('uzytkownicy', function (Blueprint $table) {
            $table->id('id_uzytkownika');
            $table->string('nazwa', 255);
            $table->string('email')->unique();
            $table->string('rola', 50)->default('PRACOWNIK'); // ADMIN/KIEROWNIK/PRACOWNIK
            $table->boolean('aktywny')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uzytkownicy');
        Schema::dropIfExists('faktury_pozycje');
        Schema::dropIfExists('faktury_sprzedazy');
        Schema::dropIfExists('zamowienia_pozycje');
        Schema::dropIfExists('zamowienia');
        Schema::dropIfExists('dostawcy');
        Schema::dropIfExists('kontrahenci');
        Schema::dropIfExists('klienci');
        Schema::dropIfExists('ruchy_magazynowe');
        Schema::dropIfExists('stany_magazynowe');
        Schema::dropIfExists('lokalizacje');
        Schema::dropIfExists('magazyny');
        Schema::dropIfExists('ceny_produktow');
        Schema::dropIfExists('produkty');
        Schema::dropIfExists('producenci');
        Schema::dropIfExists('kategorie');
    }
};
