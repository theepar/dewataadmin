<?php

namespace App\Filament\Resources\WebsiteApiKeyResource\Pages;

use App\Filament\Resources\WebsiteApiKeyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWebsiteApiKeys extends ListRecords
{
    protected static string $resource = WebsiteApiKeyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
