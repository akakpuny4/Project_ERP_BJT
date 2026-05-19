<?php

namespace App\Filament\Resources\BarangKeluarResource\Pages;

use App\Filament\Resources\BarangKeluarResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBarangKeluar extends CreateRecord
{
    protected static string $resource = BarangKeluarResource::class;

    // 🛡️ PROTEKSI 2: Mutasi data form tepat sebelum diserahkan ke Model Eloquent
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Memastikan nilai dari input form dipaksa menjadi integer positif murni
        if (isset($data['jumlah_keluar'])) {
            $data['jumlah_keluar'] = abs((int) $data['jumlah_keluar']);
        }

        return $data;
    }
}