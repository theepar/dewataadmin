<?php

namespace App\Filament\Resources;

// --- MODEL ---
use Filament\Forms\Form;

// --- FILAMENT RESOURCE & KOMPONEN ---
use App\Models\IcalEvent;
use App\Models\VillaUnit;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Artisan;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;

// --- LARAVEL FACADE ---
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\VillaUnitResource\Pages;

class VillaUnitResource extends Resource
{
    protected static ?string $model = VillaUnit::class;
    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationGroup = 'Properti';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('villa_id')
                    ->label('Villa')
                    ->relationship('villa', 'name')
                    ->required()
                    ->searchable(false),

                TextInput::make('unit_number')
                    ->label('Unit Number')
                    ->required()
                    ->maxLength(50),

                TextInput::make('ical_link')
                    ->label('iCal Link')
                    ->url()
                    ->nullable()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('villa.name')->label('Villa')->searchable()->sortable(),
                TextColumn::make('unit_number')->label('Unit Number')->searchable(),
                TextColumn::make('ical_link')->label('iCal Link')->copyable()->limit(40),
                TextColumn::make('last_synced_at')->label('Last Synced At')->dateTime()->sortable(),
            ])
            ->headerActions([
                Action::make('sync_all_icals')
                    ->label('Sync All iCal')
                    ->icon('heroicon-o-arrow-path') // ganti icon yang tersedia
                    ->requiresConfirmation()
                    ->action(function () {
                        // jalankan command sync tanpa unit_id untuk sync semua unit
                        Artisan::call('ical:sync');
                        Notification::make()
                            ->title('Sync selesai')
                            ->success()
                            ->body('Sync iCal semua unit sudah dijalankan.')
                            ->send();
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(), // tombol Edit
                DeleteAction::make()->requiresConfirmation(), // tombol Delete per baris
                Action::make('preview_ical')
                    ->label('Preview iCal')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Preview Event iCal')
                    ->modalButton('Tutup')
                    ->modalContent(function ($record) {
                        // Sync dulu data iCal untuk unit ini
                        Artisan::call('ical:sync', [
                            'unit_id' => $record->id,
                        ]);

                        // Ambil event dari database berdasarkan unit id
                        $events = IcalEvent::where('villa_unit_id', $record->id)
                            ->orderBy('start_date', 'asc')
                            ->get()
                            ->map(function ($ev) {
                                // Format tanggal ke Asia/Makassar
                                $ev->start_date = $ev->start_date
                                    ? $ev->start_date->setTimezone('Asia/Makassar')->format('d M Y H:i')
                                    : null;
                                $ev->end_date = $ev->end_date
                                    ? $ev->end_date->setTimezone('Asia/Makassar')->format('d M Y H:i')
                                    : null;
                                return $ev->toArray();
                            })->toArray();

                        return view('filament.modals.ical-preview', [
                            'events' => $events,
                        ]);
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                // ...empty state actions...
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
            'index' => Pages\ListVillaUnits::route('/'),
            'create' => Pages\CreateVillaUnit::route('/create'),
            'edit' => Pages\EditVillaUnit::route('/{record}/edit'),
        ];
    }

    public static function isGloballySearchable(): bool
    {
        return false;
    }
}
