<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kuitansi Pengeluaran</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; margin: 0; padding: 10px 20px; }
        .dotted-line { border-bottom: 1px dotted #000; padding-left: 5px; }
        td { vertical-align: middle; padding: 4px 0; }
    </style>
</head>
<body>

    <table style="width: 100%; border-bottom: 2px solid black; margin-bottom: 15px; padding-bottom: 5px;">
        <tr>
            <td style="width: 50%; font-weight: bold;">
                <span style="font-size: 16px;">CV BASTIONG JAYA TRADE</span><br>
                <span style="font-size: 11px;">Jl. Rawasari I No. 3. Bastiong Talangame, RT 09/RW 03.. TERNATE</span><br>
                <span style="font-size: 11px;">Telp : 085240196399</span>
            </td>
            <td style="width: 50%; text-align: left; padding-left: 20px; vertical-align: top;">
                <div style="font-size: 16px; font-weight: bold; margin-bottom: 8px; text-align: center;">KUITANSI PENGELUARAN CV BJT</div>
                <table style="width: 100%; font-size: 12px; font-weight: bold;">
                    <tr><td width="20%">Nomor</td><td width="5%">:</td><td>{{ $no_kuitansi }}</td></tr>
                    <tr><td>Tanggal</td><td>:</td><td>{{ date('d - m - Y', strtotime($data->tanggal)) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table style="width: 100%;">
        <tr>
            <td width="30%" style="font-weight: bold;">Sudah Terima Uang dari</td>
            <td width="2%">:</td>
            <td style="font-weight: bold; font-size: 14px; padding-left: 5px;">CV BASTIONG JAYA TRADE</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Uang Sejumlah</td>
            <td>:</td>
            <td style="padding: 6px 0;">
                <div style="border: 2px solid #000; padding: 6px 12px; font-weight: bold; font-size: 13px; background-color: #f8f9fa;">
                    # {{ strtoupper($terbilang) }} #
                </div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top; font-weight: bold; padding-top: 10px;">Untuk Pembelian / Pembayaran</td>
            <td style="vertical-align: top; padding-top: 10px;">:</td>
            <td style="padding-top: 10px;">
                <div class="dotted-line" style="min-height: 18px;">{{ $data->keperluan }}</div>
                <div class="dotted-line" style="min-height: 18px;">@if($data->pembelian) Barang: {{ $data->pembelian->barang->nama_barang }} ({{ $data->pembelian->jumlah }} {{ $data->pembelian->satuan }}) @endif</div>
                <div class="dotted-line" style="min-height: 18px;"></div>
                <div class="dotted-line" style="min-height: 18px;"></div>
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-top: 25px;">
        <tr>
            <td colspan="3">
                <div style="font-size: 18px; font-weight: bold; padding: 8px 15px; border-bottom: 4px solid #000; border-top: 4px solid #000; display: inline-block; margin-bottom: 20px;">
                    Rp. {{ number_format($data->jumlah, 0, ',', '.') }}
                </div>
            </td>
        </tr>
        <tr style="font-weight: bold;">
            <td style="width: 33%; text-align: center; vertical-align: top;">
                <span>Setuju Dibayar</span>
                <div style="height: 60px;"></div>
                <span style="text-decoration: underline;">( .................................................. )</span>
            </td>
            <td style="width: 33%; text-align: center; vertical-align: top;">
                <span>Dibayar Oleh</span>
                <div style="height: 60px;"></div>
                <span style="text-decoration: underline;">( .................................................. )</span>
            </td>
            <td style="width: 33%; text-align: center; vertical-align: top;">
                <span>Penerima</span>
                <div style="height: 60px;"></div>
                <span style="text-decoration: underline;">( {{ $data->penerima ?? '.......................................' }} )</span>
            </td>
        </tr>
    </table>

</body>
</html>