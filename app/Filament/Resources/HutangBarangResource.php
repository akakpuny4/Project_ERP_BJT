<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HutangBarangResource\Pages;
use App\Models\HutangBarang;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

class HutangBarangResource extends Resource
{
    protected static ?string $model = HutangBarang::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-x-mark';
    protected static ?string $navigationLabel = '3.7.1 Hutang Barang';
    protected static ?string $modelLabel = 'Riwayat Hutang Barang';
    protected static ?string $navigationGroup = '4. Hutang & Piutang';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No. Urut')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_kreditur')
                    ->label('Nama Supplier')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('uraian')
                    ->label('Uraian')
                    ->wrap(),

                Tables\Columns\TextColumn::make('saldo_sebelumnya')
                    ->label('Saldo Sebelumnya')
                    ->money('IDR')
                    ->prefix('Rp '),

                Tables\Columns\TextColumn::make('kredit')
                    ->label('Hutang Baru (Kredit)')
                    ->money('IDR')
                    ->prefix('Rp ')
                    ->color('danger'),

                Tables\Columns\TextColumn::make('debet')
                    ->label('Pelunasan (Debet)')
                    ->money('IDR')
                    ->prefix('Rp ')
                    ->color('success'),

                Tables\Columns\TextColumn::make('saldo_hutang')
                    ->label('Sisa Hutang')
                    ->money('IDR')
                    ->prefix('Rp ')
                    ->weight('bold')
                    ->color('primary'),
            ])

            // ✅ TAMBAHAN ACTION BAYAR CICILAN
            ->actions([
                Tables\Actions\Action::make('bayar_cicilan')
                    ->label('Bayar Cicilan')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')

                    ->form([
                        \Filament\Forms\Components\TextInput::make('jumlah_bayar')
                            ->label('Jumlah yang Ingin Dibayar')
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
                            ->label('Gunakan Rekening (Sumber Uang)')
                            ->options(\App\Models\Rekening::pluck('nama_rekening', 'id'))
                            ->required(),
                    ])

                    ->action(function ($record, array $data) {

                        $jumlah = (int) str_replace('.', '', $data['jumlah_bayar']);
                        $sisa = $record->saldo_hutang - $jumlah;
                        $status_lunas = $sisa <= 0 ? ' (LUNAS)' : ' (Cicilan)';

                        // Mengekstrak uraian agar jika ada cicilan berkali-kali, keterangannya tetap rapi
                        $base_uraian = 'Pembayaran hutang ke ' . $record->nama_kreditur;

                        // 1. Tambah histori hutang
                        \App\Models\HutangBarang::create([
                            'tanggal' => now(),
                            'nama_kreditur' => $record->nama_kreditur,
                            'uraian' => $base_uraian . $status_lunas,
                            'saldo_sebelumnya' => $record->saldo_hutang,
                            'kredit' => 0,
                            'debet' => $jumlah,
                            'saldo_hutang' => (float) $sisa,
                            'pembelian_id' => $record->pembelian_id,
                        ]);

                        // 2. Masuk ke Pengeluaran
                        \App\Models\Pengeluaran::create([
                            'tanggal' => now(),
                            'kuitansi' => true,
                            'keperluan' => 'Bayar Hutang' . $status_lunas,
                            'penerima' => $record->nama_kreditur,
                            'jumlah' => $jumlah,
                            'rekening_id' => $data['rekening_id'],
                            'pembelian_id' => $record->pembelian_id,
                        ]);

                        // 3. Potong saldo rekening
                        $rek = \App\Models\Rekening::find($data['rekening_id']);
                        $saldo_baru = $rek->saldo_akhir - $jumlah;

                        \App\Models\BukuKasUmum::create([
                            'rekening_id' => $rek->id,
                            'tanggal' => now(),
                            'uraian' => 'Bayar Hutang: ' . $record->nama_kreditur . $status_lunas,
                            'debet' => 0,
                            'kredit' => $jumlah,
                            'saldo' => $saldo_baru,
                        ]);

                        $rek->update([
                            'saldo_akhir' => $saldo_baru
                        ]);

                        // 4. Update pembelian
                        if ($record->pembelian_id) {
                            $pembelian = \App\Models\Pembelian::find($record->pembelian_id);

                            if ($pembelian) {
                                $pembelian->hutang = $sisa;

                                if ($sisa <= 0) {
                                    $pembelian->status_pembayaran = 'Lunas';
                                }

                                $pembelian->save();
                            }
                        }
                    })

                    // ✅ LOGIKA VISIBILITY SUPER PINTAR UNTUK HUTANG BARANG
                    ->visible(function ($record) {
                        // 1. Jika sudah LUNAS (0), Sembunyikan tombol seketika
                        if ($record->saldo_hutang <= 0) {
                            return false;
                        }

                        // 2. Karena Hutang Barang bersumber dari Pembelian, kita cari baris terakhir berdasarkan pembelian_id
                        if ($record->pembelian_id) {
                            $barisTerakhir = \App\Models\HutangBarang::where('pembelian_id', $record->pembelian_id)
                                ->latest('id')
                                ->first();
                                
                            // Tampilkan tombol HANYA jika baris ini adalah yang paling baru
                            return $barisTerakhir && $barisTerakhir->id === $record->id;
                        }

                        // Fallback (jika suatu saat ada data manual tanpa pembelian_id)
                        $base_uraian = trim(str_replace([' (LUNAS)', ' (Cicilan)'], '', $record->uraian));
                        $barisTerakhirManual = \App\Models\HutangBarang::whereNull('pembelian_id')
                            ->where('uraian', 'like', $base_uraian . '%')
                            ->latest('id')
                            ->first();

                        return $barisTerakhirManual && $barisTerakhirManual->id === $record->id;
                    }),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHutangBarangs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}