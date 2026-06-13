<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@tambakmandhala.com',
            'phone' => '081234567890',
            'password' => Hash::make('password123'),
            'tambak_name' => 'Tambak Mandhala',
            'lokasi_tambak' => 'Desa Mangunharjo, Kec. Tugu, Semarang',
            'populasi' => 7500,
            'berat_rata' => 18.5,
            'target_panen_kg' => 800,
            'target_size_gram' => 35,
            'tebar_date' => Carbon::now()->subWeeks(6),
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Siti Aminah',
            'email' => 'siti@tambakrejeki.com',
            'phone' => '082345678901',
            'password' => Hash::make('password123'),
            'tambak_name' => 'Tambak Rejeki',
            'lokasi_tambak' => 'Pemalang, Jawa Tengah',
            'populasi' => 5000,
            'berat_rata' => 12.0,
            'target_panen_kg' => 450,
            'target_size_gram' => 28,
            'tebar_date' => Carbon::now()->subWeeks(4),
            'is_active' => true,
        ]);
    }
}