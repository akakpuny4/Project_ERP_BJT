<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PiutangResource\Pages;
use App\Models\Piutang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

class PiutangResource extends Resource
{
    protected static ?string $model = Piutang::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';
    protected static ?string $navigationLabel = '3.8 Buku Piutang';
    protected static ?string $modelLabel = 'Riwayat Piutang';
    protected static ?string $navigationGroup = '4. Hutang & Piutang';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('tanggal')
                    ->label('Tanggal Piutang')
                    ->default(now())
                    ->required(),

                Forms\Components\TextInput::make('uraian')
                    ->label('Uraian (Contoh: Bpk. X hutang uang/barang)')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('debet')
                    ->label('Jumlah Piutang Baru')
                    ->prefix('Rp')
                    ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                    ->stripCharacters('.')
                    ->default(0)
                    ->required(),
            ]);
    }

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

                Tables\Columns\TextColumn::make('uraian')
                    ->wrap(),

                Tables\Columns\TextColumn::make('saldo_sebelumnya')
                    ->money('IDR')
                    ->prefix('Rp '),

                Tables\Columns\TextColumn::make('debet')
                    ->label('Piutang Baru (Debet)')
                    ->money('IDR')
                    ->prefix('Rp ')
                    ->color('danger'),

                Tables\Columns\TextColumn::make('kredit')
                    ->label('Pelunasan (Kredit)')
                    ->money('IDR')
                    ->prefix('Rp ')
                    ->color('success'),

                Tables\Columns\TextColumn::make('saldo_piutang')
                    ->label('Sisa Piutang')
                    ->money('IDR')
                    ->prefix('Rp ')
                    ->weight('bold')
                    ->color('primary'),
            ])

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
                            ->maxValue(fn ($record) => $record->saldo_piutang)
                            ->rules([
                                fn ($record): \Closure => function (string $attribute, $value, \Closure $fail) use ($record) {
                                    $nilaiBayar = (int) str_replace('.', '', $value);
                                    if ($nilaiBayar > $record->saldo_piutang) {
                                        $fail('Jumlah pembayaran tidak boleh melebihi sisa piutang (Rp ' . number_format($record->saldo_piutang, 0, ',', '.') . ').');
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
                        $sisa = $record->saldo_piutang - $jumlah;
                        $status_lunas = $sisa <= 0 ? ' (LUNAS)' : ' (Cicilan)';

                        // Mengekstrak teks awal agar history tetap menyatu (Hapus embel-embel lama)
                        $base_uraian = trim(str_replace([' (LUNAS)', ' (Cicilan)'], '', $record->uraian));

                        // 1. Tambah histori di Piutang
                        \App\Models\Piutang::create([
                            'tanggal' => now(),
                            'uraian' => $base_uraian . $status_lunas,
                            'saldo_sebelumnya' => $record->saldo_piutang,
                            'debet' => 0,
                            'kredit' => $jumlah,
                            'saldo_piutang' => (float) $sisa,
                            'penjualan_id' => $record->penjualan_id,
                        ]);

                        // 2. Sinkron ke Hutang Uang
                        if ($record->penjualan_id) {
                            $penjualan = \App\Models\Penjualan::find($record->penjualan_id);

                            if ($penjualan) {
                                $lastHutang = \App\Models\HutangUang::where('pihak_terkait', $penjualan->nama_pembeli)
                                    ->latest('id')
                                    ->first();

                                if ($lastHutang) {
                                    \App\Models\HutangUang::create([
                                        'tanggal' => now(),
                                        'pihak_terkait' => $penjualan->nama_pembeli,
                                        'uraian' => 'Pembayaran aspal ' . $status_lunas,
                                        'saldo_sebelumnya' => $lastHutang->saldo_hutang,
                                        'kredit' => 0,
                                        'debet' => $jumlah,
                                        'saldo_hutang' => (float) $sisa,
                                    ]);
                                }
                            }
                        }

                        // 3. Penerimaan
                        $terima_dari = $record->penjualan_id ? \App\Models\Penjualan::find($record->penjualan_id)->nama_pembeli : $base_uraian;

                        \App\Models\Penerimaan::create([
                            'tanggal' => now(),
                            'terima_dari' => $terima_dari,
                            'jumlah' => $jumlah,
                            'uraian' => 'Terima Pembayaran: ' . $base_uraian . $status_lunas,
                            'cara_pembayaran' => $data['cara_pembayaran'],
                            'rekening_id' => $data['rekening_id'],
                            'penjualan_id' => $record->penjualan_id,
                        ]);

                        // 4. Buku Kas Umum
                        $rek = \App\Models\Rekening::find($data['rekening_id']);
                        $saldo_baru = $rek->saldo_akhir + $jumlah;

                        \App\Models\BukuKasUmum::create([
                            'rekening_id' => $rek->id,
                            'tanggal' => now(),
                            'uraian' => 'Terima cicilan dari ' . $terima_dari . $status_lunas,
                            'debet' => $jumlah,
                            'kredit' => 0,
                            'saldo' => $saldo_baru,
                        ]);

                        $rek->update(['saldo_akhir' => $saldo_baru]);

                        // 5. Update Penjualan
                        if ($record->penjualan_id) {
                            $penjualan = \App\Models\Penjualan::find($record->penjualan_id);
                            if ($penjualan) {
                                $penjualan->piutang = $sisa;
                                if ($sisa <= 0) {
                                    $penjualan->status_pembayaran = 'Lunas';
                                }
                                $penjualan->save();
                            }
                        }
                    })

                    // LOGIKA VISIBILITY SUPER PINTAR
                    ->visible(function ($record) {
                        // 1. Jika sudah LUNAS (0), Sembunyikan tombol dimanapun itu berada!
                        if ($record->saldo_piutang <= 0) {
                            return false;
                        }

                        // 2. Jika ini dari form Penjualan (Ada ID)
                        if ($record->penjualan_id) {
                            $barisTerakhir = \App\Models\Piutang::where('penjualan_id', $record->penjualan_id)
                                ->latest('id')
                                ->first();
                                
                            // Tampilkan HANYA jika baris ini adalah baris paling terbaru
                            return $barisTerakhir && $barisTerakhir->id === $record->id;
                        }

                        // 3. Jika ini dari Piutang Manual
                        $base_uraian = trim(str_replace([' (LUNAS)', ' (Cicilan)'], '', $record->uraian));
                        
                        $barisTerakhirManual = \App\Models\Piutang::whereNull('penjualan_id')
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
            'index' => Pages\ListPiutangs::route('/'),
            'create' => Pages\CreatePiutang::route('/create'),
        ];
    }
}