<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StokBarangResource\Pages;
use App\Models\StokBarang;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;

class StokBarangResource extends Resource
{
    protected static ?string $model = StokBarang::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = '2.3 Buku Stok Barang';
    protected static ?string $modelLabel = 'Riwayat Stok';
    protected static ?string $navigationGroup = '2. Stok Barang';

    // Form dihilangkan karena data ini terisi otomatis oleh sistem (Read-Only)

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No. Urut')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('uraian')
                    ->label('Uraian')
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('barang_masuk')
                    ->label('Barang Masuk')
                    ->numeric()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('saldo_sebelumnya')
                    ->label('Saldo Sebelumnya')
                    ->numeric(),
                Tables\Columns\TextColumn::make('saldo_jual')
                    ->label('Saldo Jual')
                    ->numeric()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('jumlah_barang_keluar')
                    ->label('Barang Keluar')
                    ->numeric()
                    ->badge()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('nama_pembeli')
                    ->label('Nama Pembeli')
                    ->searchable(),
                Tables\Columns\TextColumn::make('npwp_nik')
                    ->label('NPWP/NIK')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('saldo_gudang')
                    ->label('Saldo Gudang (Fisik)')
                    ->numeric()
                    ->weight('bold')
                    ->color('primary'),
            ])
            ->defaultSort('id', 'asc')
            ->groups([
                Group::make('barang.nama_barang')
                    ->label('Tabel Buku Stok')
                    ->collapsible(),
            ])
            ->defaultGroup('barang.nama_barang')
            ->filters([
                // Filter tambahan jika ingin mencari berdasarkan tanggal
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
            ->actions([
                // Tidak ada aksi Edit/Delete karena ini buku riwayat (Ledger)
            ])
            ->bulkActions([
                // Dikosongkan untuk mencegah admin menghapus riwayat secara massal
            ]);
    }

    public static function getPages(): array
    {
        return [
            // Hanya ada halaman List (Tabel)
            'index' => Pages\ListStokBarangs::route('/'),
        ];
    }
    
    // Mencegah admin membuat data baru secara manual di menu ini
    public static function canCreate(): bool
    {
        return false;
    }
}