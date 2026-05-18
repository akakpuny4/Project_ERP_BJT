<?php

namespace App\Filament\Resources\HutangBarangResource\Pages;

use App\Filament\Resources\HutangBarangResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHutangBarang extends EditRecord
{
    protected static string $resource = HutangBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
