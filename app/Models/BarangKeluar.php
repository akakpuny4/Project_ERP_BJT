<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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
        // 🛡️ PROTEKSI 1: Cegah nilai negatif SEBELUM data disimpan ke database
        static::saving(function ($barangKeluar) {
            // Jika ada kode gaib/observer lain yang mengubah nilai menjadi minus, 
            // kita paksa nilainya kembali menjadi nilai absolut (positif)
            if ($barangKeluar->jumlah_keluar < 0) {
                // Catat ke storage/logs/laravel.log untuk melacak file perusak jika Anda ingin tahu
                Log::warning("Deteksi Cacat Logika: Ada file yang mencoba mengubah jumlah_keluar menjadi negatif ({$barangKeluar->jumlah_keluar}). Sistem otomatis memulihkannya.");
                
                $barangKeluar->jumlah_keluar = abs($barangKeluar->jumlah_keluar);
            }
        });

        // Logika pengisian Buku Stok setelah data berhasil dibuat
        static::created(function ($barangKeluar) {
            // Pastikan jumlah keluar yang masuk ke perhitungan stok adalah angka positif yang valid
            $jumlahKeluarFisik = abs($barangKeluar->jumlah_keluar);

            $stokTerakhir = StokBarang::where('barang_id', $barangKeluar->barang_id)->latest('id')->first();
            $saldo_jual_sebelumnya = $stokTerakhir ? $stokTerakhir->saldo_jual : 0;
            $saldo_gudang_sebelumnya = $stokTerakhir ? $stokTerakhir->saldo_gudang : 0;

            StokBarang::create([
                'barang_id' => $barangKeluar->barang_id,
                'tanggal' => $barangKeluar->tanggal,
                'uraian' => $barangKeluar->nama_pembeli . ' mengambil ' . $barangKeluar->barang->nama_barang,
                'barang_masuk' => 0,
                'saldo_sebelumnya' => $saldo_jual_sebelumnya,
                'saldo_jual' => $saldo_jual_sebelumnya, // Tidak berubah, sudah dipotong saat Penjualan
                'jumlah_barang_keluar' => $jumlahKeluarFisik,
                'nama_pembeli' => $barangKeluar->nama_pembeli,
                'npwp_nik' => $barangKeluar->npwp_nik,
                'saldo_gudang' => $saldo_gudang_sebelumnya - $jumlahKeluarFisik, // Potong fisik gudang secara benar
            ]);
        });
    }
}