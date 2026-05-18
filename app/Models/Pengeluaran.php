<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal', 'kuitansi', 'keperluan', 'penerima', 
        'jumlah', 'rekening_id', 'pembelian_id'
    ];

    public function rekening()
    {
        return $this->belongsTo(Rekening::class);
    }

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class);
    }

    // LOGIKA ANTI-DOUBLE ENTRY BUKU KAS UMUM
    protected static function booted()
    {
        static::created(function ($pengeluaran) {
            // Jika pembelian_id NULL, berarti ini input manual (biaya harian, bayar hutang, mutasi)
            if (is_null($pengeluaran->pembelian_id)) {
                $rekening = Rekening::find($pengeluaran->rekening_id);
                $saldo_baru = $rekening->saldo_akhir - $pengeluaran->jumlah;
                
                BukuKasUmum::create([
                    'rekening_id' => $pengeluaran->rekening_id,
                    'tanggal' => $pengeluaran->tanggal,
                    'uraian' => $pengeluaran->keperluan . ' - ' . ($pengeluaran->penerima ?? 'Manual'),
                    'debet' => 0, 
                    'kredit' => $pengeluaran->jumlah, // Kas Berkurang
                    'saldo' => $saldo_baru,
                ]);

                $rekening->update(['saldo_akhir' => $saldo_baru]);
            }
        });
    }
}