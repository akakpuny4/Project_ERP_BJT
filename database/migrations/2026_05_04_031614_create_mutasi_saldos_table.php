<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mutasi_saldos', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('rekening_sumber_id')->constrained('rekenings')->onDelete('cascade');
            $table->foreignId('rekening_tujuan_id')->constrained('rekenings')->onDelete('cascade');
            $table->string('cara_pembayaran'); // Tunai atau Transfer
            $table->decimal('jumlah', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_saldos');
    }
};
