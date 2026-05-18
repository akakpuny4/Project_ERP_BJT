<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangMasukResource\Pages;
use App\Models\BarangMasuk;
use App\Models\Pembelian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;

class BarangMasukResource extends Resource
{
    protected static ?string $model = BarangMasuk::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $navigationLabel = 'Barang Masuk';
    protected static ?string $modelLabel = 'Barang Masuk';
    protected static ?string $navigationGroup = 'Stok Barang';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('pembelian_id')
                    ->label('Pilih Data Pembelian (Referensi)')
                    ->searchable()
                    ->options(function () {
                        return \App\Models\Pembelian::with('barang')
                            ->get()
                            ->filter(function ($pembelian) {
                                $totalMasuk = \App\Models\BarangMasuk::where('pembelian_id', $pembelian->id)->sum('jumlah');
                                $sisa = $pembelian->jumlah - $totalMasuk;

                                return $sisa > 0;
                            })
                            ->mapWithKeys(function ($pembelian) {
                                $totalMasuk = \App\Models\BarangMasuk::where('pembelian_id', $pembelian->id)->sum('jumlah');
                                $sisa = $pembelian->jumlah - $totalMasuk;

                                return [
                                    $pembelian->id => "{$pembelian->nama_penjual} - {$pembelian->barang->nama_barang} (Sisa: {$sisa})"
                                ];
                            });
                    })
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        $pembelian = Pembelian::find($state);
                        if ($pembelian) {
                            $set('tanggal', $pembelian->tanggal_barang_masuk ?? $pembelian->tanggal_pesanan);
                            $set('barang_id', $pembelian->barang_id);
                            $set('satuan', $pembelian->satuan);
                            
                            $sudahMasuk = BarangMasuk::where('pembelian_id', $state)->sum('jumlah');
                            $sisa = $pembelian->jumlah - $sudahMasuk;
                            $set('sisa_kuota', $sisa);
                        }
                    }),

                Forms\Components\DatePicker::make('tanggal')
                    ->label('Tanggal Masuk')
                    ->required(),
                
                Forms\Components\Hidden::make('barang_id'),
                
                Forms\Components\TextInput::make('nama_pengangkut')
                    ->label('Nama Pengangkut'),

                Forms\Components\TextInput::make('sisa_kuota')
                    ->label('Sisa Kuota Pembelian')
                    ->readonly()
                    ->dehydrated(false)
                    ->extraInputAttributes(['style' => 'color: blue; font-weight: bold;']),

                Forms\Components\TextInput::make('jumlah')
                    ->label('Jumlah Barang Masuk')
                    ->numeric()
                    ->required()
                    ->live(onBlur: true)
                    ->rules([
                        fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                            $sisa = (int) $get('sisa_kuota');
                            if ($value > $sisa) {
                                $fail("Jumlah barang masuk melebihi sisa kuota pembelian ({$sisa}).");
                            }
                        },
                    ])
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::hitungTotalHargaJual($set, $get)),

                Forms\Components\TextInput::make('satuan')
                    ->label('Satuan')
                    ->readonly(),

                Forms\Components\TextInput::make('harga_jual_satuan')
                    ->label('Set Harga Jual Satuan')
                    ->prefix('Rupiah')
                    ->numeric()
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::hitungTotalHargaJual($set, $get)),

                Forms\Components\TextInput::make('jumlah_harga_jual')
                    ->label('Total Potensi Harga Jual')
                    ->prefix('Rupiah')
                    ->numeric()
                    ->readonly()
                    ->dehydrated(),
            ]);
    }

    public static function hitungTotalHargaJual(Set $set, Get $get)
    {
        $jumlah = (int) ($get('jumlah') ?: 0);
        $harga = (float) ($get('harga_jual_satuan') ?: 0);
        $set('jumlah_harga_jual', $jumlah * $harga);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('barang.nama_barang')->label('Barang'),
                Tables\Columns\TextColumn::make('jumlah'),
                Tables\Columns\TextColumn::make('harga_jual_satuan')->money('IDR')->prefix('Rp '),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBarangMasuks::route('/'),
            'create' => Pages\CreateBarangMasuk::route('/create'),
        ];
    }
}