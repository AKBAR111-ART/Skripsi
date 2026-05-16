<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengingatHarian extends Model
{
protected $fillable = [
    'nama_penjaga',
    'tanggal',
    'nomor_wa'
];

public function jadwal()
{
    return $this->hasMany(PengingatJadwal::class);
}
}