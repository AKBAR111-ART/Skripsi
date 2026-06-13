<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'tambak_name',
        'lokasi_tambak',
        'populasi',
        'berat_rata',
        'target_panen_kg',
        'target_size_gram',
        'tebar_date',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Hitung umur budidaya dalam minggu
    public function getUmurMingguAttribute()
    {
        if (!$this->tebar_date) return 0;
        return Carbon::parse($this->tebar_date)->diffInWeeks(Carbon::now());
    }

    // Hitung biomassa (kg)
    public function getBiomassaAttribute()
    {
        return ($this->populasi * $this->berat_rata) / 1000;
    }

    // Allow login using phone or email
    public function findForLogin($username)
    {
        return $this->where('phone', $username)
                    ->orWhere('email', $username)
                    ->first();
    }
}