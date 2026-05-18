<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penerimaans', function (Blueprint $table) {
            $table->id(); // Bertindak sebagai Nomor Urut 3.1.1
            $table->date('tanggal');
            $table->string('terima_dari');
            $table->string('npwp_nik')->nullable();
            $table->decimal('jumlah', 15, 2)->default(0);
            $table->string('uraian');
            $table->string('cara_pembayaran'); // Tunai / Transfer
            $table->foreignId('rekening_id')->constrained('rekenings')->cascadeOnDelete(); // Bank / Kas
            
            // Relasi opsional jika berasal dari penjualan
            $table->foreignId('penjualan_id')->nullable()->constrained('penjualans')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penerimaans');
    }
};