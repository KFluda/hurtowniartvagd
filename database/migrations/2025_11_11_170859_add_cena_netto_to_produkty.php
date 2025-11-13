<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('produkty', function (Blueprint $table) {
            // jeśli nie masz tej kolumny – dodaj
            if (!Schema::hasColumn('produkty', 'cena_netto')) {
                $table->decimal('cena_netto', 10, 2)->nullable()->after('stawka_vat');
            }
            // na wszelki wypadek – jeśli nie ma stawki_vat
            if (!Schema::hasColumn('produkty', 'stawka_vat')) {
                $table->decimal('stawka_vat', 5, 2)->default(23)->after('kod_sku');
            }
        });
    }

    public function down(): void
    {
        Schema::table('produkty', function (Blueprint $table) {
            if (Schema::hasColumn('produkty', 'cena_netto')) {
                $table->dropColumn('cena_netto');
            }
            // nie usuwam stawka_vat jeśli była wcześniej
        });
    }
};


