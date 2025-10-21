<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produkt extends Model
{
    protected $table = 'produkty';
    protected $primaryKey = 'id_produktu';
    public $timestamps = false;

    protected $fillable = [
        'kod_sku','ean','nazwa','id_producenta','id_kategorii',
        'waga_kg','glebokosc_mm','szerokosc_mm','wysokosc_mm',
        'gwarancja_miesiecy','czy_z_numerem_seryjnym','stawka_vat','aktywny'
    ];
}
