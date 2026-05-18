<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal_pesanan', 'tanggal_barang_masuk', 'nama_penjual', 
        'alamat', 'npwp_nip', 'kontak_person', 'barang_id', 
        'satuan', 'jumlah', 'harga_satuan', 'jumlah_harga', 
        'uang_muka', 'status_pembayaran', 'hutang', 'rekening_id'
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function rekening()
    {
        return $this->belongsTo(Rekening::class);
    }

    // LOGIKA OTOMATISASI WAJIB (TRIGGER SAAT DATA PEMBELIAN DISIMPAN)
    protected static function booted()
    {
        static::created(function ($pembelian) {
            
            $rekening = Rekening::find($pembelian->rekening_id);

            // 1. OTOMATIS MASUK KE PENGELUARAN (3.3)
            Pengeluaran::create([
                'tanggal' => $pembelian->tanggal_pesanan,
                'kuitansi' => true,
                'keperluan' => 'Pembelian Barang',
                'penerima' => $pembelian->nama_penjual,
                'jumlah' => $pembelian->uang_muka,
                'rekening_id' => $pembelian->rekening_id,
                'pembelian_id' => $pembelian->id,
            ]);

            // 2. OTOMATIS MENGURANGI REKENING & MASUK KE BUKU KAS UMUM (3.5)
            $saldo_baru = $rekening->saldo_akhir - $pembelian->uang_muka;
            
            BukuKasUmum::create([
                'rekening_id' => $pembelian->rekening_id,
                'tanggal' => $pembelian->tanggal_pesanan,
                'uraian' => 'Pembelian ' . $pembelian->barang->nama_barang . ' dari ' . $pembelian->nama_penjual,
                'debet' => 0,
                'kredit' => $pembelian->uang_muka, // Uang Keluar
                'saldo' => $saldo_baru,
            ]);

            $rekening->update(['saldo_akhir' => $saldo_baru]);

            // 3. OTOMATIS MASUK KE HUTANG BARANG JIKA STATUS HUTANG (3.7.1)
            if ($pembelian->status_pembayaran === 'Hutang' && $pembelian->hutang > 0) {
                HutangBarang::create([
                    'tanggal' => $pembelian->tanggal_pesanan,
                    'nama_kreditur' => $pembelian->nama_penjual,
                    'uraian' => 'Pembelian ' . $pembelian->barang->nama_barang . ' (hutang)',
                    'saldo_sebelumnya' => 0,
                    'kredit' => $pembelian->hutang, // Hutang Bertambah
                    'debet' => 0,
                    'saldo_hutang' => $pembelian->hutang,
                    'pembelian_id' => $pembelian->id,
                ]);
            }
        });
    }
}