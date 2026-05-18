<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembelianResource\Pages;
use App\Models\Pembelian;
use App\Models\Barang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

class PembelianResource extends Resource
{
    protected static ?string $model = Pembelian::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = '1.2 Pembelian';
    protected static ?string $modelLabel = 'Data Pembelian';
    protected static ?string $navigationGroup = '1. Pembelian & Penjualan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pesanan & Supplier')->schema([
                    Forms\Components\DatePicker::make('tanggal_pesanan')
                        ->label('Tanggal Pesanan')
                        ->required(),

                    Forms\Components\DatePicker::make('tanggal_barang_masuk')
                        ->label('Tanggal Barang Masuk'),

                    Forms\Components\TextInput::make('nama_penjual')
                        ->label('Nama Penjual (Supplier)')
                        ->required(),

                    Forms\Components\TextInput::make('npwp_nip')
                        ->label('NPWP / NIP'),

                    Forms\Components\TextInput::make('kontak_person')
                        ->label('Kontak Person'),

                    Forms\Components\Textarea::make('alamat')
                        ->label('Alamat')
                        ->columnSpanFull(),
                ])->columns(2),

                Forms\Components\Section::make('Detail Barang & Keuangan')->schema([
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
                            }
                        }),

                    Forms\Components\TextInput::make('satuan')
                        ->label('Satuan')
                        ->readonly(),

                    Forms\Components\TextInput::make('jumlah')
                        ->label('Jumlah')
                        ->numeric()
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, Get $get) => self::hitungTotal($set, $get)),

                    Forms\Components\TextInput::make('harga_satuan')
                        ->label('Harga Satuan')
                        ->prefix('Rupiah')
                        ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                        ->stripCharacters('.')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, Get $get) => self::hitungTotal($set, $get)),

                    Forms\Components\TextInput::make('jumlah_harga')
                        ->label('Jumlah Harga')
                        ->prefix('Rupiah')
                        ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                        ->stripCharacters('.')
                        ->readonly()
                        ->dehydrated()
                        ->default(0),

                    Forms\Components\TextInput::make('uang_muka')
                        ->label('Uang Muka (DP)')
                        ->prefix('Rupiah')
                        ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                        ->stripCharacters('.')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, Get $get) => self::hitungTotal($set, $get))
                        ->rules([
                            fn(Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                // Ambil jumlah_harga (sudah diformat dengan titik pemisah ribuan)
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

                    Forms\Components\TextInput::make('hutang')
                        ->label('Sisa Hutang')
                        ->prefix('Rupiah')
                        ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                        ->stripCharacters('.')
                        ->readonly()
                        ->dehydrated()
                        ->default(0),

                    Forms\Components\Select::make('rekening_id')
                        ->label('Rekening Sumber (Buku Kas Umum)')
                        ->relationship('rekening', 'nama_rekening')
                        ->required(),
                ])->columns(2),
            ]);
    }

    public static function hitungTotal(Set $set, Get $get)
    {
        $jumlah = (int) ($get('jumlah') ?: 0);

        $harga = (float) str_replace('.', '', $get('harga_satuan') ?: 0);

        $total = $jumlah * $harga;

        $set('jumlah_harga', number_format($total, 0, ',', '.'));

        $dp = (float) str_replace('.', '', $get('uang_muka') ?: 0);

        if ($dp >= $total && $total > 0) {
            $set('status_pembayaran', 'Lunas');
            $set('hutang', number_format(0, 0, ',', '.'));
        } else {
            $set('status_pembayaran', 'Hutang');
            $set('hutang', number_format($total - $dp, 0, ',', '.'));
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_pesanan')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('nama_penjual')
                    ->label('Supplier'),

                Tables\Columns\TextColumn::make('barang.nama_barang')
                    ->label('Barang'),

                Tables\Columns\TextColumn::make('jumlah'),

                Tables\Columns\TextColumn::make('status_pembayaran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Lunas' => 'success',
                        'Hutang' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('uang_muka')
                    ->money('IDR')
                    ->prefix('Rp '),

                Tables\Columns\TextColumn::make('hutang')
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
            'index' => Pages\ListPembelians::route('/'),
            'create' => Pages\CreatePembelian::route('/create'),
        ];
    }
}