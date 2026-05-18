<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjualans', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('nama_pembeli');
            $table->string('alamat')->nullable();
            $table->string('npwp_nik')->nullable();
            $table->string('kontak_person')->nullable();
            
            // Relasi ke tabel barangs
            $table->foreignId('barang_id')->constrained('barangs')->cascadeOnDelete();
            $table->string('satuan');
            $table->string('volume')->nullable();
            $table->integer('jumlah')->default(0);
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('jumlah_harga', 15, 2)->default(0); // jumlah x harga_satuan
            $table->decimal('uang_muka', 15, 2)->default(0);
            
            // Relasi ke Buku Kas Umum (Rekening Tujuan)
            $table->foreignId('rekening_id')->constrained('rekenings')->cascadeOnDelete();
            
            // Status dan Keuangan
            $table->string('status_pembayaran'); // Lunas / Hutang
            $table->decimal('piutang', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualans');
    }
};