<?php

namespace App\Filament\Resources\HutangUangResource\Pages;

use App\Filament\Resources\HutangUangResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHutangUang extends EditRecord
{
    protected static string $resource = HutangUangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
