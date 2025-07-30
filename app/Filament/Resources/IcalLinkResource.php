<?php

namespace App\Filament\Resources;

// --- NAMESPACE DASAR FILAMENT ---
use App\Filament\Resources\IcalLinkResource\Pages;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

// --- IMPORTS UNTUK KOMPONEN FILAMENT FORMS ---
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select; // Pastikan ini ada
use Filament\Forms\Components\Hidden; // Ini akan tetap dipakai jika perlu field tersembunyi lain

// --- IMPORTS UNTUK KOMPONEN FILAMENT TABLES ---
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;

// --- IMPORTS UNTUK ELOQUENT & LAINNYA ---
use App\Models\IcalLink;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth; // Untuk Auth::user()

class IcalLinkResource extends Resource
{
    protected static ?string $model = IcalLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationGroup = 'Manajemen iCal';

    public static function canAccess(): bool
    {
        return Auth::user()->hasRole('admin'); // Hanya admin yang bisa akses
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Field untuk memilih Villa terkait
                Select::make('villa_id')
                    ->label('Villa Terkait')
                    ->relationship('villa', 'name') // Mengambil nama dari model Villa
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpan(1),

                // Field Nama/Deskripsi Link
                TextInput::make('name')
                    ->label('Nama/Deskripsi Link')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),

                // Field URL iCal dari Airbnb
                TextInput::make('ical_url')
                    ->label('URL iCal dari Airbnb')
                    ->url()
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                // --- PERBAIKAN: FIELD USER_ID AGAR TERLIHAT DAN BISA DIPILIH OLEH ADMIN ---
                Select::make('user_id') // Menggunakan Select untuk menampilkan dan memilih user
                    ->label('Dikelola Oleh User') // Label yang lebih jelas
                    ->relationship('user', 'name') // Mengambil nama dari model User
                    ->default(auth()->id()) // Default ke user yang sedang login
                    ->searchable() // Bisa dicari
                    ->preload() // Memuat semua opsi di awal
                    ->required()
                    // Hanya terlihat jika user yang sedang login adalah admin
                    ->visible(fn (): bool => Auth::user()->hasRole('admin')),
                // --- AKHIR PERBAIKAN USER_ID ---
            ])->columns(2); // Mengatur layout form menjadi 2 kolom
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('villa.name')
                    ->label('Villa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Link')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->sortable(),
                TextColumn::make('last_synced_at')
                    ->label('Terakhir Sinkron')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ical_url')
                    ->label('URL iCal')
                    ->copyable()
                    ->limit(40)
                    ->tooltip(fn (string $state): string => $state)
                    ->toggleable(isToggledHiddenByDefault: true), // Sembunyikan default
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('villa')
                    ->relationship('villa', 'name')
                    ->label('Filter Berdasarkan Villa'),
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->label('Filter Berdasarkan User'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListIcalLinks::route('/'),
            'create' => Pages\CreateIcalLink::route('/create'),
            'edit' => Pages\EditIcalLink::route('/{record}/edit'),
        ];
    }
}