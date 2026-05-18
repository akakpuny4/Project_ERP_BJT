<?php

namespace App\Filament\Resources\MutasiSaldoResource\Pages;

use App\Filament\Resources\MutasiSaldoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMutasiSaldo extends EditRecord
{
    protected static string $resource = MutasiSaldoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
