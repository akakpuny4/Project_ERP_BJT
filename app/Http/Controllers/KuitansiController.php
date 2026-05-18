<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penerimaan;
use App\Models\Pengeluaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class KuitansiController extends Controller
{
    private function getBulanRomawi($bulan)
    {
        $map = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
        return $map[(int)$bulan];
    }

    private function penyebut($nilai) {
        $nilai = abs($nilai);
        $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
        $temp = "";
        if ($nilai < 12) {
            $temp = " ". $huruf[$nilai];
        } else if ($nilai < 20) {
            $temp = $this->penyebut($nilai - 10). " Belas";
        } else if ($nilai < 100) {
            $temp = $this->penyebut($nilai/10)." Puluh". $this->penyebut($nilai % 10);
        } else if ($nilai < 200) {
            $temp = " Seratus" . $this->penyebut($nilai - 100);
        } else if ($nilai < 1000) {
            $temp = $this->penyebut($nilai/100) . " Ratus" . $this->penyebut($nilai % 100);
        } else if ($nilai < 2000) {
            $temp = " Seribu" . $this->penyebut($nilai - 1000);
        } else if ($nilai < 1000000) {
            $temp = $this->penyebut($nilai/1000) . " Ribu" . $this->penyebut($nilai % 1000);
        } else if ($nilai < 1000000000) {
            $temp = $this->penyebut($nilai/1000000) . " Juta" . $this->penyebut($nilai % 1000000);
        } else if ($nilai < 1000000000000) {
            $temp = $this->penyebut($nilai/1000000000) . " Milyar" . $this->penyebut(fmod($nilai, 1000000000));
        }
        return $temp;
    }

    private function terbilang($nilai) {
        if($nilai < 0) {
            $hasil = "minus ". trim($this->penyebut($nilai));
        } else {
            $hasil = trim($this->penyebut($nilai));
        }
        return $hasil . " Rupiah";
    }

    public function pemasukan($id)
    {
        $data = Penerimaan::with('penjualan.barang')->findOrFail($id);
        
        $no_urut = str_pad($data->id, 3, '0', STR_PAD_LEFT);
        $nama_barang = $data->penjualan ? Str::slug($data->penjualan->barang->nama_barang) : 'umum';
        $bulan = $this->getBulanRomawi(date('m', strtotime($data->tanggal)));
        $tahun = date('y', strtotime($data->tanggal));
        
        $no_kuitansi = "{$no_urut}/Ku.{$nama_barang}/CV BJT/{$bulan}/{$tahun}";
        $nama_file = "Kuitansi_Pemasukan_" . str_replace('/', '_', $no_kuitansi) . ".pdf";
        
        $terbilang = $this->terbilang($data->jumlah);

        // MENGUBAH UKURAN KERTAS MENJADI A4 NORMAL (PORTRAIT)
        $pdf = Pdf::loadView('pdf.kuitansi-pemasukan', compact('data', 'no_kuitansi', 'terbilang'))
                  ->setPaper('a4', 'portrait');
                  
        return $pdf->stream($nama_file);
    }

    public function pengeluaran($id)
    {
        $data = Pengeluaran::with('pembelian.barang')->findOrFail($id);
        
        $no_urut = str_pad($data->id, 3, '0', STR_PAD_LEFT);
        $nama_barang = $data->pembelian ? Str::slug($data->pembelian->barang->nama_barang) : 'umum';
        $bulan = $this->getBulanRomawi(date('m', strtotime($data->tanggal)));
        $tahun = date('y', strtotime($data->tanggal));
        
        $no_kuitansi = "{$no_urut}/Ku.{$nama_barang}/CV BJT/{$bulan}/{$tahun}";
        $nama_file = "Kuitansi_Pengeluaran_" . str_replace('/', '_', $no_kuitansi) . ".pdf";

        $terbilang = $this->terbilang($data->jumlah);

        // MENGUBAH UKURAN KERTAS MENJADI A4 NORMAL (PORTRAIT)
        $pdf = Pdf::loadView('pdf.kuitansi-pengeluaran', compact('data', 'no_kuitansi', 'terbilang'))
                  ->setPaper('a4', 'portrait');
                  
        return $pdf->stream($nama_file);
    }
}