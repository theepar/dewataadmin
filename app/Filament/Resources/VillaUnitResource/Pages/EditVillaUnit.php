<?php

namespace App\Filament\Resources\VillaUnitResource\Pages;

use App\Filament\Resources\VillaUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVillaUnit extends EditRecord
{
    protected static string $resource = VillaUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
