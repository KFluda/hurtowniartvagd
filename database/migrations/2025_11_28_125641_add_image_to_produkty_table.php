<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produkty', function (Blueprint $table) {
            // Zdjęcie produktu – nazwa pliku (np. tv55.jpg)
            $table->string('image')->nullable()->after('nazwa');
        });
    }

    public function down(): void
    {
        Schema::table('produkty', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
