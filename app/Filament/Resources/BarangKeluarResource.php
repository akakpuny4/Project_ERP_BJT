<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangKeluarResource\Pages;
use App\Models\BarangKeluar;
use App\Models\Penjualan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;

class BarangKeluarResource extends Resource
{
    protected static ?string $model = BarangKeluar::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationLabel = '2.2 Barang Keluar';
    protected static ?string $modelLabel = 'Barang Keluar';
    protected static ?string $navigationGroup = '2. Stok Barang';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('penjualan_id')
                    ->label('Pilih Data Penjualan (Referensi)')
                    ->searchable()
                    ->options(function () {
                        return \App\Models\Penjualan::with('barang')
                            ->get()
                            ->filter(function ($penjualan) {
                                $totalKeluar = \App\Models\BarangKeluar::where('penjualan_id', $penjualan->id)->sum('jumlah_keluar');
                                $sisa = $penjualan->jumlah - $totalKeluar;

                                return $sisa > 0;
                            })
                            ->mapWithKeys(function ($penjualan) {
                                $totalKeluar = \App\Models\BarangKeluar::where('penjualan_id', $penjualan->id)->sum('jumlah_keluar');
                                $sisa = $penjualan->jumlah - $totalKeluar;

                                return [
                                    $penjualan->id => "{$penjualan->nama_pembeli} - {$penjualan->barang->nama_barang} (Sisa: {$sisa})"
                                ];
                            });
                    })
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        $penjualan = Penjualan::find($state);
                        if ($penjualan) {
                            $set('barang_id', $penjualan->barang_id);
                            $set('nama_pembeli', $penjualan->nama_pembeli);
                            $set('alamat', $penjualan->alamat);
                            $set('npwp_nik', $penjualan->npwp_nik);
                            $set('tanggal_transaksi', $penjualan->tanggal);
                            $set('kontak_person', $penjualan->kontak_person);
                            
                            $sudahKeluar = BarangKeluar::where('penjualan_id', $state)->sum('jumlah_keluar');
                            $sisa = $penjualan->jumlah - $sudahKeluar;
                            $set('sisa_ambil', $sisa);
                        }
                    }),

                Forms\Components\DatePicker::make('tanggal')
                    ->label('Tanggal Keluar Fisik')
                    ->default(now())
                    ->required(),
                
                Forms\Components\Hidden::make('barang_id'),
                
                Forms\Components\TextInput::make('nama_pembeli')->readonly(),
                Forms\Components\TextInput::make('npwp_nik')->readonly(),
                Forms\Components\DatePicker::make('tanggal_transaksi')->readonly(),
                Forms\Components\TextInput::make('kontak_person')->readonly(),
                Forms\Components\Textarea::make('alamat')->readonly()->columnSpanFull(),

                Forms\Components\TextInput::make('sisa_ambil')
                    ->label('Sisa Barang Boleh Diambil')
                    ->readonly()
                    ->dehydrated(false)
                    ->extraInputAttributes(['style' => 'color: blue; font-weight: bold;']),

                Forms\Components\TextInput::make('jumlah_keluar')
                    ->label('Jumlah Diambil')
                    ->numeric()
                    ->required()
                    ->rules([
                        fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                            $sisa = (int) $get('sisa_ambil');
                            if ($value > $sisa) {
                                $fail("Jumlah keluar melebihi sisa jatah penjualan ({$sisa}).");
                            }
                        },
                    ]),
                    
                Forms\Components\TextInput::make('alat_angkut')
                    ->label('Alat Angkut / Plat Nomor')
                    ->placeholder('Contoh: Truk DT 1234 XX'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('nama_pembeli'),
                Tables\Columns\TextColumn::make('barang.nama_barang'),
                Tables\Columns\TextColumn::make('jumlah_keluar'),
                Tables\Columns\TextColumn::make('alat_angkut'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBarangKeluars::route('/'),
            'create' => Pages\CreateBarangKeluar::route('/create'),
        ];
    }
}