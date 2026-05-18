<?php

namespace App\Filament\Resources\HutangUangResource\Pages;

use App\Filament\Resources\HutangUangResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHutangUangs extends ListRecords
{
    protected static string $resource = HutangUangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
