<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penerimaan extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal', 'terima_dari', 'npwp_nik', 'jumlah', 
        'uraian', 'cara_pembayaran', 'rekening_id', 'penjualan_id'
    ];

    public function rekening()
    {
        return $this->belongsTo(Rekening::class);
    }

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    // LOGIKA ANTI-DOUBLE ENTRY BUKU KAS UMUM
    protected static function booted()
    {
        static::created(function ($penerimaan) {
            // Jika penjualan_id NULL, berarti ini input manual (seperti pinjaman/mutasi)
            if (is_null($penerimaan->penjualan_id)) {
                $rekening = Rekening::find($penerimaan->rekening_id);
                $saldo_baru = $rekening->saldo_akhir + $penerimaan->jumlah;
                
                BukuKasUmum::create([
                    'rekening_id' => $penerimaan->rekening_id,
                    'tanggal' => $penerimaan->tanggal,
                    'uraian' => $penerimaan->uraian . ' (Input Manual)',
                    'debet' => $penerimaan->jumlah, // Kas Bertambah
                    'kredit' => 0,
                    'saldo' => $saldo_baru,
                ]);

                $rekening->update(['saldo_akhir' => $saldo_baru]);
            }
        });
    }
}