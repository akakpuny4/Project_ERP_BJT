<?php

namespace App\Filament\Resources\MutasiSaldoResource\Pages;

use App\Filament\Resources\MutasiSaldoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMutasiSaldos extends ListRecords
{
    protected static string $resource = MutasiSaldoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
