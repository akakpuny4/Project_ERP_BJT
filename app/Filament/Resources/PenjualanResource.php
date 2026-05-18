<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenjualanResource\Pages;
use App\Models\Penjualan;
use App\Models\Barang;
use App\Models\StokBarang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Support\RawJs;
use Illuminate\Validation\ClosureValidationRule;

class PenjualanResource extends Resource
{
    protected static ?string $model = Penjualan::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = '1.1 Penjualan';
    protected static ?string $modelLabel = 'Data Penjualan';
    protected static ?string $navigationGroup = '1. Pembelian & Penjualan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pembeli')->schema([
                    Forms\Components\DatePicker::make('tanggal')
                        ->label('Tanggal Transaksi')
                        ->required()
                        ->default(now()),

                    Forms\Components\TextInput::make('nama_pembeli')
                        ->label('Nama Pembeli')
                        ->required(),

                    Forms\Components\TextInput::make('npwp_nik')
                        ->label('NPWP / NIK'),

                    Forms\Components\TextInput::make('kontak_person')
                        ->label('Kontak Person'),

                    Forms\Components\Textarea::make('alamat')
                        ->label('Alamat')
                        ->columnSpanFull(),
                ])->columns(2),

                Forms\Components\Section::make('Detail Barang & Pembayaran')->schema([
                    Forms\Components\Select::make('barang_id')
                        ->label('Nama Barang')
                        ->relationship('barang', 'nama_barang')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, ?string $state) {
                            $barang = Barang::find($state);

                            if ($barang) {
                                $set('satuan', $barang->satuan);

                                // Cek stok saat ini
                                $stokTerakhir = StokBarang::where('barang_id', $state)
                                    ->latest('id')
                                    ->first();

                                $saldoJual = $stokTerakhir ? $stokTerakhir->saldo_jual : 0;

                                $set(
                                    'info_stok',
                                    'Stok Tersedia: ' . number_format($saldoJual, 0, ',', '.') . ' ' . $barang->satuan
                                );
                            }
                        }),

                    Forms\Components\TextInput::make('info_stok')
                        ->label('Informasi Stok')
                        ->readonly()
                        ->dehydrated(false)
                        ->extraInputAttributes([
                            'style' => 'color: red; font-weight: bold;',
                        ]),

                    Forms\Components\TextInput::make('satuan')
                        ->label('Satuan')
                        ->readonly(),

                    Forms\Components\TextInput::make('volume')
                        ->label('Volume')
                        ->placeholder('Contoh: 10 Drum'),

                    Forms\Components\TextInput::make('jumlah')
                        ->label('Jumlah')
                        ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                        ->stripCharacters('.')
                        ->default(0)
                        ->live(onBlur: true)
                        ->required()
                        ->rules([
                            fn(Get $get): \Closure => function (
                                string $attribute,
                                $value,
                                \Closure $fail
                            ) use ($get) {

                                $barang_id = $get('barang_id');

                                if (!$barang_id) {
                                    return;
                                }

                                $stokTerakhir = StokBarang::where('barang_id', $barang_id)
                                    ->latest('id')
                                    ->first();

                                $saldoJual = $stokTerakhir ? $stokTerakhir->saldo_jual : 0;

                                $nilaiJumlah = (int) str_replace('.', '', $value);

                                if ($nilaiJumlah > $saldoJual) {
                                    $fail("Jumlah penjualan melebihi saldo stok jual ({$saldoJual}). Tidak bisa menjual barang kosong!");
                                }
                            },
                        ])
                        ->afterStateUpdated(fn(Set $set, Get $get) => self::hitungTotal($set, $get)),

                    Forms\Components\TextInput::make('harga_satuan')
                        ->label('Harga Satuan')
                        ->prefix('Rupiah')
                        ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                        ->stripCharacters('.')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn(Set $set, Get $get) => self::hitungTotal($set, $get)),

                    Forms\Components\TextInput::make('jumlah_harga')
                        ->label('Jumlah Harga')
                        ->prefix('Rupiah')
                        ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                        ->stripCharacters('.')
                        ->readonly()
                        ->dehydrated()
                        ->default(0),

                    Forms\Components\TextInput::make('uang_muka')
                        ->label('Uang Muka (Pembayaran)')
                        ->prefix('Rupiah')
                        ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                        ->stripCharacters('.')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn(Set $set, Get $get) => self::hitungTotal($set, $get))
                        ->rules([
                            fn(Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                // Ambil nilai jumlah_harga, hilangkan pemisah ribuan
                                $jumlahHarga = (int) str_replace('.', '', $get('jumlah_harga') ?: 0);
                                $nilaiUangMuka = (int) str_replace('.', '', $value);

                                if ($nilaiUangMuka > $jumlahHarga && $jumlahHarga > 0) {
                                    $fail('Uang muka tidak boleh lebih besar dari total harga.');
                                }
                            },
                        ]),

                    Forms\Components\TextInput::make('status_pembayaran')
                        ->label('Status')
                        ->readonly()
                        ->dehydrated()
                        ->default('Hutang'),

                    Forms\Components\TextInput::make('piutang')
                        ->label('Sisa Piutang (Kekurangan)')
                        ->prefix('Rupiah')
                        ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                        ->stripCharacters('.')
                        ->readonly()
                        ->dehydrated()
                        ->default(0),

                    Forms\Components\Select::make('rekening_id')
                        ->label('Rekening Tujuan (Buku Kas Umum)')
                        ->relationship('rekening', 'nama_rekening')
                        ->required(),
                ])->columns(2),
            ]);
    }

    public static function hitungTotal(Set $set, Get $get)
    {
        $jumlah = (int) str_replace('.', '', $get('jumlah') ?: 0);

        $harga = (float) str_replace('.', '', $get('harga_satuan') ?: 0);

        $total = $jumlah * $harga;

        $set('jumlah_harga', number_format($total, 0, ',', '.'));

        $dp = (float) str_replace('.', '', $get('uang_muka') ?: 0);

        if ($dp >= $total && $total > 0) {
            $set('status_pembayaran', 'Lunas');
            $set('piutang', number_format(0, 0, ',', '.'));
        } else {
            $set('status_pembayaran', 'Hutang');
            $set('piutang', number_format($total - $dp, 0, ',', '.'));
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('nama_pembeli')
                    ->label('Pembeli'),

                Tables\Columns\TextColumn::make('barang.nama_barang')
                    ->label('Barang'),

                Tables\Columns\TextColumn::make('jumlah'),

                Tables\Columns\TextColumn::make('status_pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Lunas' => 'success',
                        'Hutang' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('jumlah_harga')
                    ->money('IDR')
                    ->prefix('Rp '),

                Tables\Columns\TextColumn::make('piutang')
                    ->money('IDR')
                    ->prefix('Rp '),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenjualans::route('/'),
            'create' => Pages\CreatePenjualan::route('/create'),
        ];
    }
}