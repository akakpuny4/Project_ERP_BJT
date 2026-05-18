<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutasiSaldo extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relasi ke rekening sumber
    public function rekeningSumber()
    {
        return $this->belongsTo(Rekening::class, 'rekening_sumber_id');
    }

    // Relasi ke rekening tujuan
    public function rekeningTujuan()
    {
        return $this->belongsTo(Rekening::class, 'rekening_tujuan_id');
    }

    // LOGIKA EFEK DOMINO
    protected static function booted()
    {
        static::created(function ($mutasi) {
            $sumber = Rekening::find($mutasi->rekening_sumber_id);
            $tujuan = Rekening::find($mutasi->rekening_tujuan_id);
            
            $uraian = "Mutasi saldo dari {$sumber->nama_rekening} ke {$tujuan->nama_rekening}";

            // 1. OTOMATIS CATAT SEBAGAI PENGELUARAN (Memotong saldo sumber & Buat Kuitansi)
            Pengeluaran::create([
                'tanggal' => $mutasi->tanggal,
                'kuitansi' => true,
                'keperluan' => $uraian,
                'penerima' => $tujuan->nama_rekening,
                'rekening_id' => $sumber->id,
                'jumlah' => $mutasi->jumlah,
            ]);

            // 2. OTOMATIS CATAT SEBAGAI PENERIMAAN (Menambah saldo tujuan)
            Penerimaan::create([
                'tanggal' => $mutasi->tanggal,
                'terima_dari' => $sumber->nama_rekening,
                'npwp_nik' => '-',
                'cara_pembayaran' => $mutasi->cara_pembayaran,
                'rekening_id' => $tujuan->id,
                'jumlah' => $mutasi->jumlah,
                'uraian' => $uraian,
            ]);

            // Catatan: Karena kita men-trigger Pengeluaran dan Penerimaan,
            // Maka saldo di Buku Kas Umum (3.5) akan otomatis terpotong dan bertambah 
            // berkat sistem yang sudah kita bangun di Tahap 12!
        });
    }
}