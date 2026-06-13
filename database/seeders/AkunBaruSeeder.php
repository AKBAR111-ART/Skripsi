<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AkunBaruSeeder extends Seeder
{
    public function run(): void
    {
        // Cek apakah email sudah ada
        $existingUser = DB::table('users')
            ->where('email', 'dimasnuril354@gmail.com')
            ->orWhere('phone', '62895379348181')
            ->first();

        if ($existingUser) {
            $this->command->info('Akun sudah ada! Update password...');
            
            // Update password jika akun sudah ada
            DB::table('users')
                ->where('id', $existingUser->id)
                ->update([
                    'password' => Hash::make('akbar354'),
                    'updated_at' => now(),
                ]);
            
            $this->command->info('Password berhasil diupdate!');
        } else {
            // Buat akun baru
            DB::table('users')->insert([
                'name' => 'Dimas Nuril',
                'email' => 'dimasnuril354@gmail.com',
                'phone' => '62895379348181',
                'password' => Hash::make('akbar354'),
                'tambak_name' => 'Tambak Berkah',
                'lokasi_tambak' => 'Desa Mangunharjo, Kec. Tugu, Semarang',
                'populasi' => 10000,
                'berat_rata' => 15.5,
                'target_panen_kg' => 1000,
                'target_size_gram' => 35,
                'tebar_date' => Carbon::now()->subWeeks(4),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->command->info('Akun baru berhasil ditambahkan!');
        }
    }
}