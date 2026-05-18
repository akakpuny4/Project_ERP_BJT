<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MutasiSaldoResource\Pages;
use App\Models\MutasiSaldo;
use App\Models\Rekening;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

class MutasiSaldoResource extends Resource
{
    protected static ?string $model = MutasiSaldo::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = '3.9 Mutasi Saldo';
    protected static ?string $modelLabel = 'Mutasi Saldo';
    protected static ?string $navigationGroup = '3. Keuangan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('tanggal')
                    ->label('Tanggal Mutasi')
                    ->default(now())
                    ->required(),

                Forms\Components\Select::make('cara_pembayaran')
                    ->label('Cara Pemindahan')
                    ->options([
                        'Transfer' => 'Transfer',
                        'Tunai' => 'Tunai',
                    ])
                    ->default('Transfer')
                    ->required(),

                Forms\Components\Select::make('rekening_sumber_id')
                    ->label('Ambil Dari Rekening (Sumber)')
                    ->options(Rekening::pluck('nama_rekening', 'id'))
                    ->required()
                    ->different('rekening_tujuan_id')
                    ->validationMessages([
                        'different' => 'Rekening tujuan tidak boleh sama dengan rekening sumber.',
                    ]),

                Forms\Components\Select::make('rekening_tujuan_id')
                    ->label('Ke Rekening Tujuan')
                    ->options(Rekening::pluck('nama_rekening', 'id'))
                    ->required(),

                Forms\Components\TextInput::make('jumlah')
                    ->label('Jumlah Uang Mutasi')
                    ->prefix('Rp')
                    ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                    ->stripCharacters('.')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('rekeningSumber.nama_rekening')
                    ->label('Sumber Uang')
                    ->color('danger')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('rekeningTujuan.nama_rekening')
                    ->label('Rekening Tujuan')
                    ->color('success')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('cara_pembayaran')
                    ->badge(),

                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Nominal')
                    ->money('IDR')
                    ->prefix('Rp ')
                    ->weight('bold'),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMutasiSaldos::route('/'),
            'create' => Pages\CreateMutasiSaldo::route('/create'),
        ];
    }
}