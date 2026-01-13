<?php

namespace App\Filament\Resources\VillaCategoryResource\Pages;

use App\Filament\Resources\VillaCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVillaCategories extends ListRecords
{
    protected static string $resource = VillaCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
