<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal', 'nama_pembeli', 'alamat', 'npwp_nik', 'kontak_person', 
        'barang_id', 'satuan', 'volume', 'jumlah', 'harga_satuan', 
        'jumlah_harga', 'uang_muka', 'rekening_id', 'status_pembayaran', 'piutang'
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function rekening()
    {
        return $this->belongsTo(Rekening::class);
    }

    // LOGIKA OTOMATISASI WAJIB
    protected static function booted()
    {
        static::created(function ($penjualan) {
            
            $rekening = Rekening::find($penjualan->rekening_id);

            // 1. MASUK KE PENERIMAAN (3.1)
            Penerimaan::create([
                'tanggal' => $penjualan->tanggal,
                'terima_dari' => $penjualan->nama_pembeli,
                'npwp_nik' => $penjualan->npwp_nik,
                'jumlah' => $penjualan->uang_muka,
                'uraian' => 'Penjualan ' . $penjualan->barang->nama_barang . ' (' . $penjualan->status_pembayaran . ')',
                'cara_pembayaran' => str_contains(strtolower($rekening->nama_rekening), 'tunai') ? 'Tunai' : 'Transfer',
                'rekening_id' => $penjualan->rekening_id,
                'penjualan_id' => $penjualan->id,
            ]);

            // 2. MASUK KE BUKU KAS UMUM (3.5)
            $saldo_baru = $rekening->saldo_akhir + $penjualan->uang_muka;
            BukuKasUmum::create([
                'rekening_id' => $penjualan->rekening_id,
                'tanggal' => $penjualan->tanggal,
                'uraian' => 'Penjualan ' . $penjualan->barang->nama_barang . ' ke ' . $penjualan->nama_pembeli,
                'debet' => $penjualan->uang_muka,
                'kredit' => 0,
                'saldo' => $saldo_baru,
            ]);
            $rekening->update(['saldo_akhir' => $saldo_baru]);

            // 3. JIKA HUTANG: MASUK KE PIUTANG (3.8) & HUTANG UANG (3.7.2) SEKALIGUS
            if ($penjualan->status_pembayaran === 'Hutang' && $penjualan->piutang > 0) {
                
                // A. Masuk ke Buku Piutang
                Piutang::create([
                    'tanggal' => $penjualan->tanggal,
                    'uraian' => 'Penjualan ' . $penjualan->barang->nama_barang . ' ke ' . $penjualan->nama_pembeli . ' (hutang)',
                    'saldo_sebelumnya' => 0,
                    'debet' => $penjualan->piutang,
                    'kredit' => 0,
                    'saldo_piutang' => $penjualan->piutang,
                    'penjualan_id' => $penjualan->id,
                ]);

                // B. Masuk ke Hutang Uang / Pelanggan (Permintaan Khusus)
                HutangUang::create([
                    'tanggal' => $penjualan->tanggal,
                    'pihak_terkait' => $penjualan->nama_pembeli,
                    'uraian' => 'Penjualan 1.1: ' . $penjualan->nama_pembeli . ' hutang ' . $penjualan->barang->nama_barang,
                    'saldo_sebelumnya' => 0,
                    'kredit' => $penjualan->piutang, // Hutang dicatat di kredit
                    'debet' => 0,
                    'saldo_hutang' => $penjualan->piutang,
                ]);
            }

            // 4. POTONG SALDO JUAL PADA BUKU STOK (2.3)
            $stokTerakhir = StokBarang::where('barang_id', $penjualan->barang_id)->latest('id')->first();
            $saldo_jual_sebelumnya = $stokTerakhir ? $stokTerakhir->saldo_jual : 0;
            $saldo_gudang_sebelumnya = $stokTerakhir ? $stokTerakhir->saldo_gudang : 0;

            StokBarang::create([
                'barang_id' => $penjualan->barang_id,
                'tanggal' => $penjualan->tanggal,
                'uraian' => $penjualan->nama_pembeli . ' membeli ' . $penjualan->barang->nama_barang,
                'barang_masuk' => 0,
                'saldo_sebelumnya' => $saldo_jual_sebelumnya,
                'saldo_jual' => $saldo_jual_sebelumnya - $penjualan->jumlah,
                'jumlah_barang_keluar' => 0,
                'nama_pembeli' => $penjualan->nama_pembeli,
                'npwp_nik' => $penjualan->npwp_nik,
                'saldo_gudang' => $saldo_gudang_sebelumnya,
            ]);
        });
    }
}