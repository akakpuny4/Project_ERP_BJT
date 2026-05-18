<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Membuat akun Admin utama untuk login
        User::factory()->create([
            'name' => 'Admin Utama',
            'email' => 'admin@cvbjt.com',
            'password' => Hash::make('password'),
        ]);

        // Memanggil seeder daftar barang
        $this->call([
            BarangSeeder::class,
            RekeningSeeder::class,
        ]);
        
    }
}