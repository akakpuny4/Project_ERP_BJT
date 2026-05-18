<?php

namespace App\Filament\Resources\BukuKasUmumResource\Pages;

use App\Filament\Resources\BukuKasUmumResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBukuKasUmums extends ListRecords
{
    protected static string $resource = BukuKasUmumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(), <-- Hapus ini
        ];
    }
}