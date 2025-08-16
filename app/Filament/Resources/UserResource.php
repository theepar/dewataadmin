<?php

namespace App\Filament\Resources;

// --- NAMESPACE DASAR FILAMENT ---
use App\Models\User;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;

// --- IMPORTS UNTUK KOMPONEN FILAMENT FORMS & TABLES ---
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;

// --- IMPORTS UNTUK HALAMAN RESOURCE ---
use App\Filament\Resources\UserResource\Pages;

// --- IMPORTS LAINNYA ---
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class UserResource extends Resource
{
    protected static ?string $model           = User::class;
    protected static ?string $navigationIcon  = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'Account';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('email')->email()->required(),
                TextInput::make('password')
                    ->password()
                    ->minLength(8)
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create')
                    ->visible(fn() => auth()->user()->hasRole('admin')),
                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->label('Roles')
                    ->visible(auth()->user()?->hasRole('admin')), // hanya admin yang bisa lihat field ini
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('roles.name')->badge(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                Action::make('sendResetPassword')
                    ->label('Kirim Link Reset Password')
                    ->icon('heroicon-o-envelope')
                    ->visible(fn() => auth()->user()->hasRole('pegawai'))
                    ->action(function ($record) {
                        $status = Password::sendResetLink(['email' => $record->email]);
                        if ($status === Password::RESET_LINK_SENT) {
                            Notification::make()
                                ->title('Link reset password berhasil dikirim ke email user. Berlaku 1 menit.')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Gagal mengirim link reset password.')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return parent::getEloquentQuery();
        }

        // Jika bukan admin, hanya tampilkan data user sendiri
        return parent::getEloquentQuery()->where('id', $user->id);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('admin');
    }
}
