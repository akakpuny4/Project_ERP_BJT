<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BukuKasUmum extends Model
{
    use HasFactory;

    protected $table = 'buku_kas_umums';
    protected $guarded = [];

    public function rekening()
    {
        return $this->belongsTo(Rekening::class);
    }

    // SMART ROUTER: Mencegat dan mengarahkan data
    protected static function booted()
    {
        static::creating(function ($bku) {
            $rekening = Rekening::find($bku->rekening_id);
            
            // Cek apakah rekening yang digunakan mengandung kata "Kas Harian"
            if ($rekening && str_contains(strtolower($rekening->nama_rekening), 'kas harian')) {
                
                // 1. Copy (Lempar) datanya ke tabel Buku Kas Harian
                BukuKasHarian::create([
                    'tanggal' => $bku->tanggal,
                    'uraian' => $bku->uraian,
                    'debet' => $bku->debet,
                    'kredit' => $bku->kredit,
                    'saldo' => $bku->saldo,
                ]);

                // 2. Batalkan penyimpanan di Buku Kas Umum (Agar tidak double data)
                return false; 
            }
        });
    }
}