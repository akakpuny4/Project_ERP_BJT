<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BukuKasUmumResource\Pages;
use App\Models\BukuKasUmum;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;

class BukuKasUmumResource extends Resource
{
    protected static ?string $model = BukuKasUmum::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = '3.5 Buku Kas Umum';
    protected static ?string $modelLabel = 'Mutasi Kas Umum';
    protected static ?string $navigationGroup = '3. Keuangan';

    // Form dihilangkan karena mutasi ini terisi otomatis oleh sistem

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('uraian')
                    ->label('Uraian Transaksi')
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
            ->groups([
                Group::make('rekening.nama_rekening')
                    ->label('Buku Rekening')
                    ->collapsible(),
            ])
            ->defaultGroup('rekening.nama_rekening')
            ->filters([
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('dari_tanggal'),
                        \Filament\Forms\Components\DatePicker::make('sampai_tanggal'),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    })
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBukuKasUmums::route('/'),
        ];
    }

    // Mencegah admin membuat data secara manual (Anti-manipulasi)
    public static function canCreate(): bool
    {
        return false;
    }
}