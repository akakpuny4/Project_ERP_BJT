<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Rekening;
use App\Models\Penjualan;
use App\Models\Pembelian;
use App\Models\HutangUang;
use App\Models\StokBarang;
use Illuminate\Support\Facades\DB;

class Neraca extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationLabel = '4. Neraca Keuangan';
    protected static ?string $title = 'Laporan Neraca (Aktiva & Pasiva)';
    protected static ?string $navigationGroup = '5. Laporan';

    protected static string $view = 'filament.pages.neraca';

    protected function getViewData(): array
    {
        // 1. AKTIVA: Kas (Uang Tunai)
        $kas = Rekening::where('nama_rekening', 'like', '%Tunai%')->sum('saldo_akhir');

        // 2. AKTIVA: Uang di Bank (Selain Tunai dan Rekening Virtual Piutang)
        $bank = Rekening::where('nama_rekening', 'not like', '%Tunai%')
                        ->where('nama_rekening', 'not like', '%Piutang%')
                        ->sum('saldo_akhir');

        // 3. AKTIVA: Jumlah Piutang (Dari sisa piutang penjualan yang belum lunas)
        $piutang = Penjualan::where('status_pembayaran', 'Hutang')->sum('piutang');

        // 4. AKTIVA: Persediaan Barang (Total fisik barang di gudang)
        $stok_terakhir_ids = StokBarang::select(DB::raw('MAX(id) as last_id'))->groupBy('barang_id')->pluck('last_id');
        $persediaan_fisik = StokBarang::whereIn('id', $stok_terakhir_ids)->sum('saldo_gudang');

        // 5. PASIVA: Hutang Barang (Dari sisa hutang pembelian) & Hutang Uang
        $hutang_barang = Pembelian::where('status_pembayaran', 'Hutang')->sum('hutang');
        
        $hutang_uang_terakhir_ids = HutangUang::select(DB::raw('MAX(id) as last_id'))->groupBy('pihak_terkait')->pluck('last_id');
        $hutang_uang = HutangUang::whereIn('id', $hutang_uang_terakhir_ids)->sum('saldo_hutang');

        $total_hutang = $hutang_barang + $hutang_uang;

        // Total Aktiva (Secara finansial Kas + Bank + Piutang)
        $total_aktiva_uang = $kas + $bank + $piutang;

        return [
            'kas' => $kas,
            'bank' => $bank,
            'piutang' => $piutang,
            'persediaan_fisik' => $persediaan_fisik,
            'total_hutang' => $total_hutang,
            'total_aktiva_uang' => $total_aktiva_uang,
        ];
    }
}