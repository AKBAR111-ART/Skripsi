<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TambakProfile extends Model
{
    protected $table = 'tambak_profile';
    
    protected $fillable = [
        'nama_tambak', 'lokasi', 'luas', 'tipe_tambak', 
        'tanggal_dibuat', 'tanggal_mulai_budidaya', 
        'populasi', 'avg_weight', 'foto_tambak'
    ];
}