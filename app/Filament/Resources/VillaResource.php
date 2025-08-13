<?php
namespace App\Filament\Resources;

// --- NAMESPACE DASAR FILAMENT ---
use App\Filament\Resources\VillaResource\Pages;
use App\Models\Villa;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MultiSelect;
use Filament\Forms\Components\Repeater;

// --- IMPORTS UNTUK KOMPONEN FILAMENT FORMS ---
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin');
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

                        MultiSelect::make('ownership_status')
                            ->label('Status Kepemilikan')
                            ->options([
                                'Freehold'  => 'Freehold',
                                'Leasehold' => 'Leasehold',
                                'Monthly'   => 'Monthly',
                            ])
                            ->required()
                            ->searchable(),

                        TextInput::make('price_idr')
                            ->label('Harga (IDR)')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),

                        TextInput::make('location')
                            ->label('URL Maps')
                            ->placeholder('https://maps.google.com/...')
                            ->columnSpanFull()
                            ->helperText('Tempelkan URL Google Maps lokasi villa di sini.'),

                        RichEditor::make('description')
                            ->label('Deskripsi Villa')
                            ->columnSpanFull()
                            ->nullable(),

                        // Tambahkan input kamar, bed, bathroom
                        TextInput::make('bedroom')
                            ->label('Jumlah Bedroom')
                            ->numeric()
                            ->minValue(0)
                            ->default(1)
                            ->required(),

                        TextInput::make('bed')
                            ->label('Jumlah Bed')
                            ->numeric()
                            ->minValue(0)
                            ->default(1)
                            ->required(),

                        TextInput::make('bathroom')
                            ->label('Jumlah Bathroom')
                            ->numeric()
                            ->minValue(0)
                            ->default(1)
                            ->required(),

                        TextInput::make('unit_count')
                            ->label('Jumlah Unit')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->dehydrated(false)
                            ->formatStateUsing(fn($state, $record) => $record ? $record->units()->count() : 1),

                        Select::make('users')
                            ->label('Pegawai yang bisa melihat villa ini')
                            ->multiple(false)
                            ->relationship('users', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Admin bisa melihat semua villa, pegawai hanya villa yang di-assign.'),
                    ])->columns(2),

                Section::make('Media Villa')
                    ->schema([
                        FileUpload::make('images')
                            ->label('Gambar')
                            ->multiple()
                            ->maxFiles(20)
                            ->image()
                            ->directory('villa-images')
                            ->disk('public')
                            ->preserveFilenames()
                            ->helperText('Drag & drop hingga 20 gambar tambahan.')
                            ->default(fn($record) =>
                                $record
                                ? $record->media()->where('type', 'image')->pluck('file_path')->values()->toArray()
                                : []
                            ),
                    ]),

                Section::make('Fasilitas Villa')
                    ->schema([
                        Repeater::make('amenities')
                            ->label('Amenities')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Amenity Name'),
                                Toggle::make('available')
                                    ->label('Available')
                                    ->default(true),
                            ])
                            ->addActionLabel('Tambah Amenity')
                            ->minItems(1)
                            ->columns(2),
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
                        optional($record->media()->where('type', 'image')->first())->file_path
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
                    ->sortable()
                    ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', $state) : $state),
                TextColumn::make('price_idr')
                    ->label('Harga (IDR)')
                    ->money('idr')
                    ->sortable(),
                TextColumn::make('units_count')
                    ->label('Jumlah Unit')
                    ->getStateUsing(fn($record) => $record->units()->count())
                    ->sortable(),
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
                        'Monthly'   => 'Monthly',
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
                    ->modalContent(fn($record) => view('filament.modals.villa-preview', [
                        'villa'    => $record,
                        'statuses' => is_array($record->ownership_status)
                        ? $record->ownership_status
                        : (is_string($record->ownership_status) && ! empty($record->ownership_status)
                            ? explode(',', $record->ownership_status)
                            : []),
                    ])),
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

    public static function isGloballySearchable(): bool
    {
        return false;
    }
}
