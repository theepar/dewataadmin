<?php

namespace App\Filament\Resources\IcalLinkResource\Pages;

use App\Filament\Resources\IcalLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIcalLinks extends ListRecords
{
    protected static string $resource = IcalLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
