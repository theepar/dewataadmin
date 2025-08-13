<?php

namespace App\Filament\Resources\LoginHistoryResource\Pages;

use App\Filament\Resources\LoginHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLoginHistory extends CreateRecord
{
    protected static string $resource = LoginHistoryResource::class;
}
