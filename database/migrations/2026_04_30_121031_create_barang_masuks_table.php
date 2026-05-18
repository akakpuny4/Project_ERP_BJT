<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_masuks', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            // WAJIB terhubung ke Pembelian (1.2)
            $table->foreignId('pembelian_id')->constrained('pembelians')->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('barangs')->cascadeOnDelete();
            
            $table->string('nama_pengangkut')->nullable();
            $table->integer('jumlah')->default(0);
            $table->string('satuan');
            
            // Harga Jual diinput manual saat barang masuk
            $table->decimal('harga_jual_satuan', 15, 2)->default(0);
            $table->decimal('jumlah_harga_jual', 15, 2)->default(0); // jumlah x harga_jual_satuan
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_masuks');
    }
};