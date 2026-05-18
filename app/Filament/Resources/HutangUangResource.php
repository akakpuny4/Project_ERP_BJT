<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HutangUangResource\Pages;
use App\Models\HutangUang;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

class HutangUangResource extends Resource
{
    protected static ?string $model = HutangUang::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Hutang Pelanggan (Beli Aspal)';
    protected static ?string $modelLabel = 'Hutang Pelanggan';
    protected static ?string $navigationGroup = 'Hutang & Piutang';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No. Urut')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pihak_terkait')
                    ->label('Nama Pelanggan (Penghutang)')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('uraian')
                    ->label('Keterangan Barang')
                    ->wrap(),

                Tables\Columns\TextColumn::make('saldo_sebelumnya')
                    ->money('IDR')
                    ->prefix('Rp '),

                Tables\Columns\TextColumn::make('kredit')
                    ->label('Hutang Baru / Bertambah')
                    ->money('IDR')
                    ->prefix('Rp ')
                    ->color('danger'),

                Tables\Columns\TextColumn::make('debet')
                    ->label('Telah Dibayar (Cicil)')
                    ->money('IDR')
                    ->prefix('Rp ')
                    ->color('success'),

                Tables\Columns\TextColumn::make('saldo_hutang')
                    ->label('Sisa Hutang Pelanggan')
                    ->money('IDR')
                    ->prefix('Rp ')
                    ->weight('bold')
                    ->color('primary'),
            ])

            // ✅ ACTION TERIMA CICILAN SINKRONISASI 2 ARAH
            ->actions([
                Tables\Actions\Action::make('terima_cicilan')
                    ->label('Terima Cicilan')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')

                    ->form([
                        \Filament\Forms\Components\TextInput::make('jumlah_bayar')
                            ->label('Jumlah Uang Diterima')
                            ->prefix('Rp')
                            ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                            ->stripCharacters('.')
                            ->required()
                            ->maxValue(fn ($record) => $record->saldo_hutang)
                            ->rules([
                                fn ($record): \Closure => function (string $attribute, $value, \Closure $fail) use ($record) {
                                    $nilaiBayar = (int) str_replace('.', '', $value);
                                    if ($nilaiBayar > $record->saldo_hutang) {
                                        $fail('Jumlah pembayaran tidak boleh melebihi sisa hutang (Rp ' . number_format($record->saldo_hutang, 0, ',', '.') . ').');
                                    }
                                },
                            ]),

                        \Filament\Forms\Components\Select::make('rekening_id')
                            ->label('Masuk ke Rekening Tujuan')
                            ->options(\App\Models\Rekening::pluck('nama_rekening', 'id'))
                            ->required(),

                        \Filament\Forms\Components\Select::make('cara_pembayaran')
                            ->options([
                                'Tunai' => 'Tunai',
                                'Transfer' => 'Transfer'
                            ])
                            ->required(),
                    ])

                    ->action(function ($record, array $data) {

                        $jumlah = (int) str_replace('.', '', $data['jumlah_bayar']);
                        $sisa = $record->saldo_hutang - $jumlah;
                        $status_lunas = $sisa <= 0 ? ' (LUNAS)' : ' (Cicilan)';

                        // Ekstrak base uraian agar history thread tidak terputus
                        $base_uraian = trim(str_replace([' (LUNAS)', ' (Cicilan)'], '', $record->uraian));

                        // 1. Tambah histori hutang uang
                        \App\Models\HutangUang::create([
                            'tanggal' => now(),
                            'pihak_terkait' => $record->pihak_terkait,
                            'uraian' => $base_uraian . $status_lunas,
                            'saldo_sebelumnya' => $record->saldo_hutang,
                            'kredit' => 0,
                            'debet' => $jumlah,
                            'saldo_hutang' => (float) $sisa,
                        ]);

                        // 2. Sinkron ke Piutang
                        $piutang = \App\Models\Piutang::where('uraian', 'like', '%' . $record->pihak_terkait . '%')
                            ->latest('id')
                            ->first();

                        $penjualan_id = $piutang ? $piutang->penjualan_id : null;

                        if ($piutang) {
                            $base_uraian_piutang = trim(str_replace([' (LUNAS)', ' (Cicilan)'], '', $piutang->uraian));
                            
                            \App\Models\Piutang::create([
                                'tanggal' => now(),
                                'uraian' => $base_uraian_piutang . $status_lunas,
                                'saldo_sebelumnya' => $piutang->saldo_piutang,
                                'debet' => 0,
                                'kredit' => $jumlah,
                                'saldo_piutang' => (float) $sisa,
                                'penjualan_id' => $penjualan_id,
                            ]);
                        }

                        // 3. Masuk ke Penerimaan
                        \App\Models\Penerimaan::create([
                            'tanggal' => now(),
                            'terima_dari' => $record->pihak_terkait,
                            'jumlah' => $jumlah,
                            'uraian' => 'Terima Pembayaran: ' . $base_uraian . $status_lunas,
                            'cara_pembayaran' => $data['cara_pembayaran'],
                            'rekening_id' => $data['rekening_id'],
                            'penjualan_id' => $penjualan_id,
                        ]);

                        // 4. Buku Kas Umum
                        $rek = \App\Models\Rekening::find($data['rekening_id']);
                        $saldo_baru = $rek->saldo_akhir + $jumlah;

                        \App\Models\BukuKasUmum::create([
                            'rekening_id' => $rek->id,
                            'tanggal' => now(),
                            'uraian' => 'Terima cicilan dari ' . $record->pihak_terkait . $status_lunas,
                            'debet' => $jumlah,
                            'kredit' => 0,
                            'saldo' => $saldo_baru,
                        ]);

                        $rek->update([
                            'saldo_akhir' => $saldo_baru
                        ]);

                        // 5. Update penjualan
                        if ($penjualan_id) {
                            $penjualan = \App\Models\Penjualan::find($penjualan_id);

                            if ($penjualan) {
                                $penjualan->piutang = $sisa;

                                if ($sisa <= 0) {
                                    $penjualan->status_pembayaran = 'Lunas';
                                }

                                $penjualan->save();
                            }
                        }
                    })

                    // ✅ LOGIKA VISIBILITY SUPER PINTAR UNTUK HUTANG UANG
                    ->visible(function ($record) {
                        // 1. Jika sudah LUNAS (0), Sembunyikan tombol dimanapun itu berada!
                        if ($record->saldo_hutang <= 0) {
                            return false;
                        }

                        // 2. Cari baris terakhir untuk pihak_terkait ini dengan thread uraian yang sama
                        $base_uraian = trim(str_replace([' (LUNAS)', ' (Cicilan)'], '', $record->uraian));
                        
                        $barisTerakhir = \App\Models\HutangUang::where('pihak_terkait', $record->pihak_terkait)
                            ->where('uraian', 'like', $base_uraian . '%')
                            ->latest('id')
                            ->first();

                        // Tampilkan tombol HANYA jika baris ini adalah baris paling terbaru
                        return $barisTerakhir && $barisTerakhir->id === $record->id;
                    }),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHutangUangs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}