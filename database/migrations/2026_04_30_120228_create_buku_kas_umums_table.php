<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buku_kas_umums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekening_id')->constrained('rekenings')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('uraian');
            $table->decimal('debet', 15, 2)->default(0); // Pemasukan
            $table->decimal('kredit', 15, 2)->default(0); // Pengeluaran
            $table->decimal('saldo', 15, 2)->default(0); // Saldo setelah transaksi ini
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buku_kas_umums');
    }
};