<?php

namespace App\Filament\Resources\NeracaResource\Pages;

use App\Filament\Resources\NeracaResource;
use Filament\Resources\Pages\Page;

class Neraca extends Page
{
    protected static string $resource = NeracaResource::class;

    protected static string $view = 'filament.resources.neraca-resource.pages.neraca';
}
