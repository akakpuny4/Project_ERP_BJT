<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kuitansi Pemasukan</title>
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
                <span style="font-size: 11px;">JUAL ASPAL DAN BAHAN BANGUNAN</span><br>
                <span style="font-size: 11px;">Jl. Rawasari No. 3. Bastiong Talangame. Ternate</span><br>
                <span style="font-size: 11px;">Telp/WA : 085240196399</span>
            </td>
            <td style="width: 50%; text-align: left; padding-left: 20px; vertical-align: top;">
                <div style="font-size: 18px; font-weight: bold; margin-bottom: 8px; letter-spacing: 4px; text-align: center;">K U I T A N S I</div>
                <table style="width: 100%; font-size: 12px; font-weight: bold;">
                    <tr><td width="20%">Nomor</td><td width="5%">:</td><td>{{ $no_kuitansi }}</td></tr>
                    <tr><td>Tanggal</td><td>:</td><td>{{ date('d - m - Y', strtotime($data->tanggal)) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table style="width: 100%;">
        <tr>
            <td colspan="3" style="font-weight: bold; padding-bottom: 8px;">Sudah Terima Uang dari :</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Nama / Perusahaan</td>
            <td width="2%">:</td>
            <td class="dotted-line" style="font-weight: bold;">{{ $data->terima_dari }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">NIK / NPWP</td>
            <td>:</td>
            <td class="dotted-line">{{ $data->npwp_nik ?? '.....................................................................' }}</td>
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
            <td style="font-weight: bold;">Untuk Pembayaran</td>
            <td>:</td>
            <td class="dotted-line">{{ $data->uraian }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Jumlah</td>
            <td>:</td>
            <td class="dotted-line">{{ $data->penjualan ? $data->penjualan->jumlah : '........' }} {{ $data->penjualan ? $data->penjualan->satuan : 'Drum / Zak' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Terbilang</td>
            <td>:</td>
            <td class="dotted-line" style="font-weight: bold;">{{ $data->penjualan ? strtoupper($data->penjualan->status_pembayaran) : 'LUNAS' }}</td>
        </tr>
        <tr>
            <td colspan="3" style="padding: 0;">
                <table style="width: 100%;">
                    <tr>
                        <td width="25%" style="font-weight: bold;">Harga Satuan</td>
                        <td width="2%">:</td>
                        <td width="25%">Rp. {{ number_format($data->penjualan ? $data->penjualan->harga_satuan : 0, 0, ',', '.') }}</td>
                        <td width="20%" style="font-weight: bold;">Harga Total</td>
                        <td width="2%">:</td>
                        <td>Rp. {{ number_format($data->penjualan ? $data->penjualan->jumlah_harga : 0, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Penerimaan Barang</td>
            <td>:</td>
            <td class="dotted-line">{{ $data->terima_dari }}</td>
        </tr>
    </table>

    <table style="width: 100%; margin-top: 15px;">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <div style="font-size: 18px; font-weight: bold; padding: 8px 15px; border-bottom: 4px solid #000; border-top: 4px solid #000; display: inline-block; margin-bottom: 10px;">
                    Rp. {{ number_format($data->jumlah, 0, ',', '.') }}
                </div><br>
                <span style="font-size: 12px; font-weight: bold;">Kontak Person Pembeli /No  HP :</span><br>
                <span>{{ $data->penjualan->kontak_person ?? '____________________________' }}</span>
            </td>
            <td style="width: 40%; text-align: center; vertical-align: top; font-weight: bold;">
                <span>Penerima <br> CV BASTIONG JAYA TRADE</span>
                <div style="height: 60px;"></div>
                <span style="text-decoration: underline;">( ........................................................ )</span>
            </td>
        </tr>
    </table>

</body>
</html>