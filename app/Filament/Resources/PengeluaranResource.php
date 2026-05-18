<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengeluaranResource\Pages;
use App\Models\Pengeluaran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\RawJs; // 1. TAMBAHKAN BARIS INI WAJIB!

class PengeluaranResource extends Resource
{
    protected static ?string $model = Pengeluaran::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-down';
    protected static ?string $navigationLabel = 'Pengeluaran';
    protected static ?string $modelLabel = 'Pengeluaran';
    protected static ?string $navigationGroup = 'Keuangan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('tanggal')
                    ->label('Tanggal Pengeluaran')
                    ->default(now())
                    ->required(),
                Forms\Components\Toggle::make('kuitansi')
                    ->label('Cetak Kuitansi?')
                    ->default(true),
                Forms\Components\Select::make('keperluan')
                    ->label('Keperluan Pengeluaran')
                    ->options([
                        'Bayar Hutang' => 'Bayar Hutang',
                        'Pembelian Barang' => 'Pembelian Barang',
                        'Biaya Operasional Harian' => 'Biaya Operasional Harian',
                        'Biaya Operasional Perusahaan' => 'Biaya Operasional Perusahaan',
                        'Bayar Gaji / Upah / Honor' => 'Bayar Gaji / Upah / Honor',
                        'Pembayaran Pajak' => 'Pembayaran Pajak',
                        'Mutasi Saldo (Antar Rekening)' => 'Mutasi Saldo (Antar Rekening)',
                        'Biaya CSR (Sumbangan)' => 'Biaya CSR (Sumbangan)',
                        'Lain-lain' => 'Lain-lain',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('penerima')
                    ->label('Penerima Dana')
                    ->placeholder('Contoh: Toko Alat Kantor / Buku Kas Harian'),
                Forms\Components\Select::make('rekening_id')
                    ->label('Ambil Dari Rekening (Sumber Dana)')
                    ->relationship('rekening', 'nama_rekening')
                    ->required(),
                
                // 2. PERUBAHAN PADA INPUT ANGKA ADA DI SINI
                Forms\Components\TextInput::make('jumlah')
                    ->label('Jumlah Uang Keluar')
                    ->prefix('Rp')
                    ->mask(RawJs::make('$money($input, \',\', \'.\', 0)')) // Membuat format 1.000.000 saat diketik
                    ->stripCharacters('.') // Membuang titik saat disimpan ke database agar tidak error
                    ->default(0)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('keperluan'),
                Tables\Columns\TextColumn::make('penerima'),
                Tables\Columns\TextColumn::make('rekening.nama_rekening')->label('Sumber Dana'),
                Tables\Columns\TextColumn::make('jumlah')
                    ->money('IDR')->prefix('Rp ')
                    ->color('danger')
                    ->weight('bold'),
                Tables\Columns\IconColumn::make('kuitansi')
                    ->label('Kuitansi')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\Action::make('cetak_kuitansi')
                    ->label('Cetak Kuitansi')
                    ->icon('heroicon-o-printer')
                    ->color('danger')
                    ->url(fn (Pengeluaran $record) => route('kuitansi.pengeluaran', $record->id))
                    ->openUrlInNewTab()
                    ->visible(fn (Pengeluaran $record) => $record->kuitansi),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengeluarans::route('/'),
            'create' => Pages\CreatePengeluaran::route('/create'),
        ];
    }
}