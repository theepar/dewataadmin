<?php
namespace App\Filament\Resources;

// --- NAMESPACE DASAR FILAMENT ---
use App\Filament\Resources\VillaResource\Pages;
use App\Models\Villa;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;

// --- IMPORTS UNTUK KOMPONEN FILAMENT FORMS ---
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class VillaResource extends Resource
{
    protected static ?string $model           = Villa::class;
    protected static ?string $navigationIcon  = 'heroicon-o-home';
    protected static ?string $navigationGroup = 'Properti';

    public static function canAccess(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Dasar Villa')
                    ->description('Detail utama tentang properti ini.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Villa')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('ownership_status')
                            ->label('Status Kepemilikan')
                            ->options([
                                'Freehold'  => 'Freehold',
                                'Leasehold' => 'Leasehold',
                                'Other'     => 'Lainnya',
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
                    ])->columns(2),
                Section::make('Media Villa')
                    ->schema([
                        FileUpload::make('images')
                            ->label('Gambar Villa')
                            ->multiple()
                            ->maxFiles(20)
                            ->image()
                            ->directory('villa-images')
                            ->preserveFilenames()
                            ->helperText('Drag & drop hingga 20 gambar sekaligus.')
                            ->default(fn($record) => $record?->media()->where('type', 'image')->pluck('file_path')->toArray() ?? [])
                            ->deletable(true), // agar bisa hapus langsung

                        Select::make('cover_image')
                            ->label('Pilih Cover')
                            ->options(fn($get) => collect($get('images'))->mapWithKeys(fn($img) => [$img => basename($img)]))
                            ->required()
                            ->helperText('Pilih salah satu gambar sebagai cover villa.'),

                        FileUpload::make('video')
                            ->label('Video Villa')
                            ->maxFiles(1)
                            ->maxSize(51200) // 50MB
                            ->acceptedFileTypes(['video/mp4', 'video/avi', 'video/mov'])
                            ->directory('villa-videos')
                            ->preserveFilenames()
                            ->helperText('Upload 1 video saja (mp4, avi, mov). Maksimal ukuran 50MB.')
                            ->getStateUsing(fn($record) => $record?->media()->where('type', 'video')->pluck('file_path')->toArray() ?? [])
                            ->deletable(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('media')
                    ->label('Cover')
                    ->getStateUsing(fn($record) =>
                        optional($record->media()->where('is_cover', true)->first())->file_path ?? optional($record->media()->where('type', 'image')->first())->file_path
                    )
                    ->disk('public')
                    ->circular(),
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
                SelectFilter::make('ownership_status')
                    ->label('Filter Status Kepemilikan')
                    ->options([
                        'Freehold'  => 'Freehold',
                        'Leasehold' => 'Leasehold',
                        'Other'     => 'Lainnya',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Preview Villa')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn($record) => view('filament.modals.villa-preview', ['villa' => $record])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVillas::route('/'),
            'create' => Pages\CreateVilla::route('/create'),
            'edit'   => Pages\EditVilla::route('/{record}/edit'),
        ];
    }
}
