<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rekening extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_rekening',
        'saldo_akhir',
    ];

    public function bukuKasUmum()
    {
        return $this->hasMany(BukuKasUmum::class);
    }
}