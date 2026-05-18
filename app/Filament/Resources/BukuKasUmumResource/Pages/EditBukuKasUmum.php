<?php

namespace App\Filament\Resources\BukuKasUmumResource\Pages;

use App\Filament\Resources\BukuKasUmumResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBukuKasUmum extends EditRecord
{
    protected static string $resource = BukuKasUmumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
