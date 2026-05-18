<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal', 'pembelian_id', 'barang_id', 'nama_pengangkut',
        'jumlah', 'satuan', 'harga_jual_satuan', 'jumlah_harga_jual'
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
    protected static function booted()
    {
        static::created(function ($barangMasuk) {
            $stokTerakhir = StokBarang::where('barang_id', $barangMasuk->barang_id)->latest('id')->first();
            $saldo_jual_sebelumnya = $stokTerakhir ? $stokTerakhir->saldo_jual : 0;
            $saldo_gudang_sebelumnya = $stokTerakhir ? $stokTerakhir->saldo_gudang : 0;

            StokBarang::create([
                'barang_id' => $barangMasuk->barang_id,
                'tanggal' => $barangMasuk->tanggal,
                'uraian' => $barangMasuk->barang->nama_barang . ' masuk dari ' . ($barangMasuk->nama_pengangkut ?? 'supplier'),
                'barang_masuk' => $barangMasuk->jumlah,
                'saldo_sebelumnya' => $saldo_jual_sebelumnya,
                'saldo_jual' => $saldo_jual_sebelumnya + $barangMasuk->jumlah,
                'jumlah_barang_keluar' => 0,
                'saldo_gudang' => $saldo_gudang_sebelumnya + $barangMasuk->jumlah,
            ]);
        });
    }
}