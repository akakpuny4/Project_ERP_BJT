<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenerimaanResource\Pages;
use App\Models\Penerimaan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

class PenerimaanResource extends Resource
{
    protected static ?string $model = Penerimaan::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';
    protected static ?string $navigationLabel = '3.1 Penerimaan';
    protected static ?string $modelLabel = 'Penerimaan';
    protected static ?string $navigationGroup = '3. Keuangan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('tanggal')
                    ->label('Tanggal Penerimaan')
                    ->default(now())
                    ->required(),

                Forms\Components\TextInput::make('terima_dari')
                    ->label('Terima Dari')
                    ->placeholder('Contoh: Bank Mandiri (Pinjaman) / Rek CV BJT BNI')
                    ->required(),

                Forms\Components\TextInput::make('npwp_nik')
                    ->label('NPWP / NIK (Opsional)'),

                Forms\Components\Select::make('cara_pembayaran')
                    ->label('Cara Pembayaran')
                    ->options([
                        'Tunai' => 'Tunai',
                        'Transfer' => 'Transfer',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('jumlah')
                    ->label('Jumlah Uang Masuk')
                    ->prefix('Rupiah')
                    ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                    ->stripCharacters('.')
                    ->default(0)
                    ->required(),

                Forms\Components\Select::make('rekening_id')
                    ->label('Bank / Kas Tujuan (Buku Kas Umum)')
                    ->relationship('rekening', 'nama_rekening')
                    ->required(),

                Forms\Components\Textarea::make('uraian')
                    ->label('Uraian / Keterangan')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('terima_dari'),

                Tables\Columns\TextColumn::make('rekening.nama_rekening')
                    ->label('Masuk Ke Bank/Kas'),

                Tables\Columns\TextColumn::make('jumlah')
                    ->money('IDR')
                    ->prefix('Rp ')
                    ->color('success')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('cara_pembayaran')
                    ->badge(),

                Tables\Columns\TextColumn::make('uraian')
                    ->limit(30),
            ])
            ->actions([
                Tables\Actions\Action::make('cetak_kuitansi')
                    ->label('Cetak Kuitansi')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (Penerimaan $record) => route('kuitansi.pemasukan', $record->id))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenerimaans::route('/'),
            'create' => Pages\CreatePenerimaan::route('/create'),
        ];
    }
}