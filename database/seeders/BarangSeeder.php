<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Barang;

class BarangSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nama_barang' => 'Aspal Drum Pertamina',
                'satuan' => 'Drum',
                'keterangan' => '155 kg'
            ],
            [
                'nama_barang' => 'Aspal CHPMA',
                'satuan' => 'Zak',
                'keterangan' => '25 kg'
            ],
            [
                'nama_barang' => 'Aspal Emulsi',
                'satuan' => 'Drum',
                'keterangan' => '200 kg'
            ],
            [
                'nama_barang' => 'Karet Jembatan',
                'satuan' => 'Pcs',
                'keterangan' => 'Ukuran Bervariasi'
            ],
        ];

        foreach ($data as $item) {
            Barang::create($item);
        }
    }
}