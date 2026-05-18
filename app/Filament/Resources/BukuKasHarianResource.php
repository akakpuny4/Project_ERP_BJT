<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BukuKasHarianResource\Pages;
use App\Models\BukuKasHarian;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BukuKasHarianResource extends Resource
{
    protected static ?string $model = BukuKasHarian::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static ?string $navigationLabel = '3.6 Buku Kas Harian';
    protected static ?string $modelLabel = 'Buku Kas Harian';
    protected static ?string $navigationGroup = '3. Keuangan';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('No')->sortable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('uraian')
                    ->label('Uraian Pengeluaran / Masuk')
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('debet')
                    ->label('Debet (Masuk)')
                    ->money('IDR')->prefix('Rp ')
                    ->color('success'),
                Tables\Columns\TextColumn::make('kredit')
                    ->label('Kredit (Keluar)')
                    ->money('IDR')->prefix('Rp ')
                    ->color('danger'),
                Tables\Columns\TextColumn::make('saldo')
                    ->label('Saldo Akhir')
                    ->money('IDR')->prefix('Rp ')
                    ->weight('bold')
                    ->color('primary'),
            ])
            ->defaultSort('id', 'asc')
            ->filters([
                // Filter tanggal identik dengan BKU
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBukuKasHarians::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}