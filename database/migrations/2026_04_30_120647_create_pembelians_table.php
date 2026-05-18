<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembelians', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_pesanan');
            $table->date('tanggal_barang_masuk')->nullable();
            $table->string('nama_penjual'); // Supplier
            $table->string('alamat')->nullable();
            $table->string('npwp_nip')->nullable();
            $table->string('kontak_person')->nullable();
            
            // Relasi ke tabel barangs
            $table->foreignId('barang_id')->constrained('barangs')->cascadeOnDelete();
            $table->string('satuan');
            $table->integer('jumlah')->default(0);
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('jumlah_harga', 15, 2)->default(0); // jumlah x harga_satuan
            $table->decimal('uang_muka', 15, 2)->default(0);
            
            // Status dan Keuangan
            $table->string('status_pembayaran'); // Lunas / Hutang
            $table->decimal('hutang', 15, 2)->default(0);
            
            // Relasi ke Buku Kas Umum (Rekening Sumber)
            $table->foreignId('rekening_id')->constrained('rekenings')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelians');
    }
};