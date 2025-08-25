<?php

namespace App\Filament\Resources;

// --- MODEL ---
use App\Models\LoginHistory;

// --- FILAMENT RESOURCE & COMPONENTS ---
use App\Filament\Resources\LoginHistoryResource\Pages;
use Filament\Forms\Form;
use Filament\Resources\Resource;

// --- FILAMENT TABLES ---
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

// --- LARAVEL ELOQUENT ---
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LoginHistoryResource extends Resource
{
    protected static ?string $model           = LoginHistory::class;
    protected static ?string $navigationIcon  = 'heroicon-o-arrow-right-on-rectangle';
    protected static ?string $navigationGroup = 'Account';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ip_address')->label('IP Address'),
                TextColumn::make('user_agent')->label('Device'),
                TextColumn::make('device_name')
                    ->label('Device Name')
                    ->getStateUsing(function ($record) {
                        return $record->device_name
                            ?: ($record->user_agent ? 'Browser' : '-');
                    }),
                TextColumn::make('logged_in_at')
                    ->label('Login Time')
                    ->dateTime('M d, Y H:i:s')
                    ->timezone('Asia/Makassar'), // UTC+8
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLoginHistories::route('/'),
            'create' => Pages\CreateLoginHistory::route('/create'),
            'edit'   => Pages\EditLoginHistory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
