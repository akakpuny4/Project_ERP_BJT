<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Piutang extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    // Mengkalkulasi saldo secara otomatis saat input piutang manual
    protected static function booted()
    {
        static::creating(function ($piutang) {
            // Ganti 'empty' menjadi 'is_null' agar angka 0 tidak dianggap kosong
            if (is_null($piutang->saldo_piutang)) {
                $piutang->saldo_piutang = $piutang->debet - $piutang->kredit;
            }
        });
    }
}