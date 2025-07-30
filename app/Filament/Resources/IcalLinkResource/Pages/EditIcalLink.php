<?php

namespace App\Filament\Resources\IcalLinkResource\Pages;

use App\Filament\Resources\IcalLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIcalLink extends EditRecord
{
    protected static string $resource = IcalLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
