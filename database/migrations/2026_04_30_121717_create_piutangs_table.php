<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('piutangs', function (Blueprint $table) {
            $table->id(); // Nomor Urut
            $table->date('tanggal');
            $table->string('uraian');
            $table->decimal('saldo_sebelumnya', 15, 2)->default(0);
            $table->decimal('debet', 15, 2)->default(0);  // Penambahan piutang dari 1.1
            $table->decimal('kredit', 15, 2)->default(0); // Pembayaran dari 3.1
            $table->decimal('saldo_piutang', 15, 2)->default(0);
            
            // Terhubung ke Penjualan (opsional)
            $table->foreignId('penjualan_id')->nullable()->constrained('penjualans')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('piutangs');
    }
};