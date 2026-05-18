<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangKeluar extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal', 'penjualan_id', 'barang_id', 'nama_pembeli', 'alamat',
        'npwp_nik', 'tanggal_transaksi', 'kontak_person', 'jumlah_keluar', 'alat_angkut'
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
    protected static function booted()
    {
        static::created(function ($barangKeluar) {
            $stokTerakhir = StokBarang::where('barang_id', $barangKeluar->barang_id)->latest('id')->first();
            $saldo_jual_sebelumnya = $stokTerakhir ? $stokTerakhir->saldo_jual : 0;
            $saldo_gudang_sebelumnya = $stokTerakhir ? $stokTerakhir->saldo_gudang : 0;

            StokBarang::create([
                'barang_id' => $barangKeluar->barang_id,
                'tanggal' => $barangKeluar->tanggal,
                'uraian' => $barangKeluar->nama_pembeli . ' mengambil ' . $barangKeluar->barang->nama_barang,
                'barang_masuk' => 0,
                'saldo_sebelumnya' => $saldo_jual_sebelumnya,
                'saldo_jual' => $saldo_jual_sebelumnya, // Tidak berubah, sudah dipotong saat Penjualan (1.1)
                'jumlah_barang_keluar' => $barangKeluar->jumlah_keluar,
                'nama_pembeli' => $barangKeluar->nama_pembeli,
                'npwp_nik' => $barangKeluar->npwp_nik,
                'saldo_gudang' => $saldo_gudang_sebelumnya - $barangKeluar->jumlah_keluar, // Potong fisik
            ]);
        });
    }
}