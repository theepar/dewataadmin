<?php
namespace App\Filament\Resources\IcalLinkResource\Pages;

use App\Filament\Resources\IcalLinkResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIcalLink extends CreateRecord
{
    protected static string $resource = IcalLinkResource::class;

    protected function afterCreate(): void
    {

    }
}
