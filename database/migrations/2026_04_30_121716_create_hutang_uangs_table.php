<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hutang_uangs', function (Blueprint $table) {
            $table->id(); // Nomor Urut
            $table->date('tanggal');
            $table->string('pihak_terkait'); // Kreditur/Debitur
            $table->string('uraian');
            $table->decimal('saldo_sebelumnya', 15, 2)->default(0);
            $table->decimal('kredit', 15, 2)->default(0); // Pinjam (Penambahan)
            $table->decimal('debet', 15, 2)->default(0);  // Bayar (Pengurangan)
            $table->decimal('saldo_hutang', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hutang_uangs');
    }
};