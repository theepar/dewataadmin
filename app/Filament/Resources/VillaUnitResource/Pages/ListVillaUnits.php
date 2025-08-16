<?php

namespace App\Filament\Resources\VillaUnitResource\Pages;

use App\Filament\Resources\VillaUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVillaUnits extends ListRecords
{
    protected static string $resource = VillaUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
