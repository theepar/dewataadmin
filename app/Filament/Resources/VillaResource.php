<?php

namespace App\Filament\Resources;

// --- NAMESPACE DASAR FILAMENT ---
use App\Filament\Resources\VillaResource\Pages;
use App\Models\Villa; // Model Villa Anda
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

// --- IMPORTS UNTUK KOMPONEN FILAMENT FORMS ---
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Section; // << TAMBAH: Untuk mengelompokkan field

// --- IMPORTS UNTUK KOMPONEN FILAMENT TABLES ---
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

// --- IMPORTS UNTUK ELOQUENT & LAINNYA ---
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope; // Opsional: jika menggunakan soft delete
use Illuminate\Support\Facades\Auth; // Untuk Auth::user()
use Filament\Support\Colors\Color; // Pastikan ini ada


class VillaResource extends Resource
{
    protected static ?string $model = Villa::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationGroup = 'Properti';

    public static function canAccess(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- BAGIAN 1: INFORMASI DASAR VILLA ---
                Section::make('Informasi Dasar Villa') // Judul bagian
                    ->description('Detail utama tentang properti ini.') // Deskripsi opsional
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Villa')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('ownership_status')
                            ->label('Status Kepemilikan')
                            ->options([
                                'Freehold' => 'Freehold',
                                'Leasehold' => 'Leasehold',
                                'Other' => 'Lainnya',
                            ])
                            ->required()
                            ->searchable(),

                        TextInput::make('price_idr')
                            ->label('Harga (IDR)')
                            ->numeric()
                            ->prefix('Rp')
                            ->nullable(),

                        TextInput::make('price_usd')
                            ->label('Harga (USD)')
                            ->numeric()
                            ->prefix('$')
                            ->nullable(),

                        RichEditor::make('description')
                            ->label('Deskripsi Villa')
                            ->columnSpanFull()
                            ->nullable(),
                    ])->columns(2), // Layout 2 kolom di dalam section ini

                // --- BAGIAN 2: PENGELOLAAN MEDIA (GAMBAR & VIDEO) ---
                Section::make('Media Properti') // Judul bagian media
                    ->description('Unggah gambar dan video untuk villa ini.')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('image')
                            ->collection('images')
                            ->label('Gambar Utama Villa')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                            ->maxFiles(1)
                            ->columnSpanFull(),

                        SpatieMediaLibraryFileUpload::make('gallery')
                            ->collection('gallery')
                            ->label('Galeri Foto Villa')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->multiple()
                            ->reorderable()
                            ->columnSpanFull(),

                        SpatieMediaLibraryFileUpload::make('video')
                            ->collection('videos')
                            ->label('Video Villa (Opsional)')
                            ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg'])
                            ->maxFiles(1)
                            ->columnSpanFull(),
                    ]), // Section ini tidak perlu columns() jika setiap field sudah Full
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->collection('images')
                    ->label('Gambar')
                    ->width(80)
                    ->height(80)
                    ->circular()
                    ->defaultImageUrl(url('/placeholder.png')),

                TextColumn::make('name')
                    ->label('Nama Villa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ownership_status')
                    ->label('Status Kepemilikan')
                    ->badge()
                    ->sortable(),
                TextColumn::make('price_idr')
                    ->label('Harga (IDR)')
                    ->money('idr')
                    ->sortable(),
                TextColumn::make('price_usd')
                    ->label('Harga (USD)')
                    ->money('usd')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('ownership_status')
                    ->label('Filter Status Kepemilikan')
                    ->options([
                        'Freehold' => 'Freehold',
                        'Leasehold' => 'Leasehold',
                        'Other' => 'Lainnya',
                    ]),
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
            'index' => Pages\ListVillas::route('/'),
            'create' => Pages\CreateVilla::route('/create'),
            'edit' => Pages\EditVilla::route('/{record}/edit'),
        ];
    }
}