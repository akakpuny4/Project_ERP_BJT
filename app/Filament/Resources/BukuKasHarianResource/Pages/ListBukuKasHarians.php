<?php

namespace App\Filament\Resources\BukuKasHarianResource\Pages;

use App\Filament\Resources\BukuKasHarianResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBukuKasHarians extends ListRecords
{
    protected static string $resource = BukuKasHarianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}