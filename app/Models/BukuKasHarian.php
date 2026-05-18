<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BukuKasHarian extends Model
{
    use HasFactory;

    // Buka gembok agar bisa menerima data otomatis
    protected $guarded = [];
}