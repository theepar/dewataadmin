<?php

namespace App\Filament\Resources\WebsiteApiKeyResource\Pages;

use App\Filament\Resources\WebsiteApiKeyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWebsiteApiKey extends EditRecord
{
    protected static string $resource = WebsiteApiKeyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
