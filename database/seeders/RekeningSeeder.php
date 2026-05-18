<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rekening;

class RekeningSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama_rekening' => 'Uang Tunai', 'saldo_akhir' => 0],
            ['nama_rekening' => 'Rek CV BJT BNI', 'saldo_akhir' => 0],
            ['nama_rekening' => 'Rek CV BJT BANK MANDIRI', 'saldo_akhir' => 0],
            ['nama_rekening' => 'Rek CV BJT BANK BRI', 'saldo_akhir' => 0],
            ['nama_rekening' => 'Rek BNI PINJAMAN', 'saldo_akhir' => 0],
            ['nama_rekening' => 'Rek Piutang/hutang', 'saldo_akhir' => 0],
            
            // TAMBAHAN BARU: Buku Kas Harian dimasukkan sebagai rekening permanen
            ['nama_rekening' => 'Buku Kas Harian', 'saldo_akhir' => 0],
        ];

        foreach ($data as $item) {
            Rekening::create($item);
        }
    }
}