<?php

namespace App\Filament\Resources\BukuKasHarianResource\Pages;

use App\Filament\Resources\BukuKasHarianResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBukuKasHarian extends EditRecord
{
    protected static string $resource = BukuKasHarianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
