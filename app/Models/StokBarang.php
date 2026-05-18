<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokBarang extends Model
{
    use HasFactory;

    protected $fillable = [
        'barang_id', 'tanggal', 'uraian', 'barang_masuk',
        'saldo_sebelumnya', 'saldo_jual', 'jumlah_barang_keluar',
        'nama_pembeli', 'npwp_nik', 'saldo_gudang'
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}