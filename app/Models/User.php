<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',           // nomor HP (kolom phone)
        'no_hp',           // nomor HP (kolom no_hp) - biarkan juga untuk kompatibilitas
        'tambak_name',     // nama tambak
        'lokasi_tambak',   // lokasi tambak
        'populasi',        // jumlah populasi udang
        'berat_rata',      // berat rata-rata udang
        'target_panen_kg', // target panen
        'target_size_gram',// target size
        'tebar_date',      // tanggal tebar
        'role',
        'is_verified',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_verified' => 'boolean',
        'populasi' => 'integer',
        'berat_rata' => 'float',
        'target_panen_kg' => 'float',
        'target_size_gram' => 'float',
        'tebar_date' => 'date',
    ];

    /**
     * Relasi ke tambak_profile (one to one)
     */
    public function tambakProfile()
    {
        return $this->hasOne(TambakProfile::class, 'user_id');
    }
}