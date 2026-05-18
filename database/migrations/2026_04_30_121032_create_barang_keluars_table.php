<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_keluars', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal'); // Tanggal keluar (bisa acak/manual)
            
            // WAJIB terhubung ke Penjualan (1.1)
            $table->foreignId('penjualan_id')->constrained('penjualans')->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('barangs')->cascadeOnDelete();
            
            $table->string('nama_pembeli');
            $table->string('alamat')->nullable();
            $table->string('npwp_nik')->nullable();
            $table->date('tanggal_transaksi'); // Dari 1.1.1
            $table->string('kontak_person')->nullable();
            $table->integer('jumlah_keluar')->default(0);
            $table->string('alat_angkut')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_keluars');
    }
};