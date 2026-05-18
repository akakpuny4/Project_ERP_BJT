<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengeluarans', function (Blueprint $table) {
            $table->id(); // Nomor Urut 3.3.1
            $table->date('tanggal');
            $table->boolean('kuitansi')->default(true); // Ada / Tidak ada
            $table->string('keperluan'); // Bayar Hutang, Pembelian Barang, dll
            $table->string('penerima')->nullable();
            $table->decimal('jumlah', 15, 2)->default(0);
            $table->foreignId('rekening_id')->constrained('rekenings')->cascadeOnDelete(); // Rekening Sumber
            
            // Relasi opsional jika berasal dari pembelian
            $table->foreignId('pembelian_id')->nullable()->constrained('pembelians')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengeluarans');
    }
};