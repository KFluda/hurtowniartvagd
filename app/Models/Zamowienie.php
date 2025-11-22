<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zamowienie extends Model
{
    protected $table = 'zamowienia';
    protected $primaryKey = 'id_zamowienia';
    public $timestamps = false;
    protected $fillable = [
        'numer_zamowienia',
        'id_klienta',
        'data_wystawienia',
        'status',
        'suma_netto',
        'suma_vat',
        'suma_brutto',
        'uwagi',
        'data_utworzenia',
    ];
}
