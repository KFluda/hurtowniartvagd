<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StanMagazynowy extends Model
{
    protected $table = 'stany_magazynowe';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = ['id_magazynu','id_produktu','stan','zarezerwowane'];
}
