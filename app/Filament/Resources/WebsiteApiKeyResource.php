<?php
namespace App\Filament\Resources;

use App\Filament\Resources\WebsiteApiKeyResource\Pages;
use App\Models\WebsiteApiKey;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WebsiteApiKeyResource extends Resource
{
    protected static ?string $model          = WebsiteApiKey::class;
    protected static ?string $navigationIcon = 'heroicon-o-key';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('website_name')
                ->required()
                ->label('Website Name'),
            Forms\Components\TextInput::make('api_key')
                ->required()
                ->readOnly()
                ->label('API Key')
                ->default(fn() => bin2hex(random_bytes(24))),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('website_name')->label('Website Name'),
            Tables\Columns\TextColumn::make('api_key')->label('API Key')->copyable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Created At'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWebsiteApiKeys::route('/'),
            'create' => Pages\CreateWebsiteApiKey::route('/create'),
            'edit'   => Pages\EditWebsiteApiKey::route('/{record}/edit'),
        ];
    }
}
