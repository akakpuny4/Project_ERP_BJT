<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hutang_barangs', function (Blueprint $table) {
            $table->id(); // Nomor Urut
            $table->date('tanggal');
            $table->string('nama_kreditur');
            $table->string('uraian');
            $table->decimal('saldo_sebelumnya', 15, 2)->default(0);
            $table->decimal('kredit', 15, 2)->default(0); // Hutang Baru
            $table->decimal('debet', 15, 2)->default(0);  // Pembayaran / Pengurangan
            $table->decimal('saldo_hutang', 15, 2)->default(0);
            
            // Terhubung ke Pembelian (opsional)
            $table->foreignId('pembelian_id')->nullable()->constrained('pembelians')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hutang_barangs');
    }
};