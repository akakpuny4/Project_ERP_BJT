<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_barangs', function (Blueprint $table) {
            $table->id(); // Bertindak sebagai Nomor Urut (2.3.1)
            $table->foreignId('barang_id')->constrained('barangs')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('uraian');
            
            // Logika Masuk & Saldo
            $table->integer('barang_masuk')->default(0); // Dari 2.1.3
            $table->integer('saldo_sebelumnya')->default(0);
            $table->integer('saldo_jual')->default(0); // Terhubung 1.1.9 Penjualan
            
            // Logika Keluar
            $table->integer('jumlah_barang_keluar')->default(0);
            $table->string('nama_pembeli')->nullable();
            $table->string('npwp_nik')->nullable();
            $table->integer('saldo_gudang')->default(0); // Terhubung 2.2 Barang Keluar
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_barangs');
    }
};