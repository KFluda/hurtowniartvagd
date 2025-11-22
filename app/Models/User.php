<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'uzytkownicy';
    protected $primaryKey = 'id_uzytkownika';
    public $timestamps = false;

    protected $fillable = ['email','imie_nazwisko','rola','aktywny','haslo','data_utworzenia'];
    protected $hidden = ['haslo'];

    public function getAuthPassword()
    {
        return (string) $this->haslo;
    }

    public function isActive()
    {
        return (bool)$this->aktywny;
    }
}
