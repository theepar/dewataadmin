<?php
namespace App\Filament\Resources;

use App\Filament\Resources\IcalLinkResource\Pages;
use App\Models\IcalEvent;
use App\Models\IcalLink;
use App\Models\VillaUnit;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class IcalLinkResource extends Resource
{
    protected static ?string $model           = IcalLink::class;
    protected static ?string $navigationIcon  = 'heroicon-o-link';
    protected static ?string $navigationGroup = 'Manajemen Url';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('villa_id')
                    ->label('Villa Terkait')
                    ->relationship('villa', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpan(1),

                Select::make('villa_unit_id')
                    ->label('Unit Villa')
                    ->options(function ($get) {
                        $villaId = $get('villa_id');
                        return $villaId
                        ? VillaUnit::where('villa_id', $villaId)->pluck('unit_number', 'id')
                        : [];
                    })
                    ->searchable()
                    ->required()
                    ->reactive(),

                TextInput::make('ical_url')
                    ->label('URL iCal dari Airbnb')
                    ->url()
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('villa.name')->label('Villa')->searchable()->sortable(),
                TextColumn::make('villaUnit.unit_number')->label('Unit')->sortable(),
                TextColumn::make('last_synced_at')->label('Terakhir Sinkron')->dateTime()->sortable(),
                TextColumn::make('ical_url')->label('URL iCal')->copyable()->limit(40)->tooltip(fn(string $state): string => $state),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('villa')
                    ->relationship('villa', 'name')
                    ->label('Filter Berdasarkan Villa'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Action::make('sync_ical')
                    ->label('Sync iCal')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function ($record, $livewire) {
                        $icalUrl = $record->ical_url;
                        $ics     = @file_get_contents($icalUrl);
                        if (! $ics) {
                            Notification::make()
                                ->title('Gagal mengambil data dari URL')
                                ->danger()
                                ->send();
                            return;
                        }
                        if (! class_exists('om\\IcalParser')) {
                            Notification::make()
                                ->title('Pustaka icalparser belum terpasang')
                                ->danger()
                                ->send();
                            return;
                        }
                        $parser = new \om\IcalParser();
                        $parser->parseString($ics);
                        $events   = $parser->getEvents();
                        $updated  = 0;
                        $inserted = 0;
                        foreach ($events as $ev) {
                            $uid          = $ev['UID'] ?? null;
                            $summary      = $ev['SUMMARY'] ?? null;
                            $description  = $ev['DESCRIPTION'] ?? null;
                            $start        = isset($ev['DTSTART']) ? Carbon::parse($ev['DTSTART']) : null;
                            $end          = isset($ev['DTEND']) ? Carbon::parse($ev['DTEND']) : null;
                            $status       = $ev['STATUS'] ?? null;
                            $propertyName = $ev['LOCATION'] ?? null;
                            $guestName    = $reservationId    = $guestCount    = null;
                            if (! empty($description)) {
                                if (preg_match('/Guest:\\s*(.+)/i', $description, $m)) {
                                    $guestName = trim($m[1]);
                                }
                                if (preg_match('/Reservation ID:\\s*([\\w-]+)/i', $description, $m)) {
                                    $reservationId = trim($m[1]);
                                }
                                if (preg_match('/guests?:? (\\d+)/i', $description, $m)) {
                                    $guestCount = $m[1];
                                }
                            }
                            $durasi = null;
                            if ($start && $end) {
                                $durasi = $start->diffInDays($end);
                            }
                            $isCancelled = ($status && strtolower($status) === 'cancelled');
                            $where       = [
                                'ical_link_id' => $record->id,
                                'uid'          => $uid,
                            ];
                            $data = [
                                'summary'        => $summary,
                                'description'    => $description,
                                'start_date'     => $start,
                                'end_date'       => $end,
                                'status'         => $status,
                                'guest_name'     => $guestName,
                                'reservation_id' => $reservationId,
                                'property_name'  => $propertyName,
                                'jumlah_orang'   => $guestCount,
                                'durasi'         => $durasi,
                                'is_cancelled'   => $isCancelled,
                                'villa_id'       => $record->villa_id,
                                'villa_unit_id'  => $record->villa_unit_id,
                            ];
                            $existing = IcalEvent::where($where)->first();
                            if ($existing) {
                                $isDifferent = false;
                                foreach ($data as $k => $v) {
                                    if ($existing->$k != $v) {
                                        $isDifferent = true;
                                        break;
                                    }
                                }
                                if ($isDifferent) {
                                    $existing->update($data);
                                    $updated++;
                                }
                            } else {
                                IcalEvent::create(array_merge($where, [
                                    'villa_id'      => $record->villa_id,
                                    'villa_unit_id' => $record->villa_unit_id,
                                ] + $data));
                                $inserted++;
                            }
                        }
                        $record->last_synced_at = now();
                        $record->save();
                        Notification::make()
                            ->title('Sync selesai')
                            ->success()
                            ->body("$inserted event baru, $updated event diupdate.")
                            ->send();
                    }),
                Action::make('preview_ical')
                    ->label('Preview iCal')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Preview Event iCal')
                    ->modalButton('Tutup')
                    ->modalContent(function ($record) {
                        $icalUrl = $record->ical_url;
                        $events  = [];
                        try {
                            $ics = @file_get_contents($icalUrl);
                            if ($ics) {
                                if (class_exists('om\IcalParser')) {
                                    $parser = new \om\IcalParser();
                                    $parser->parseString($ics);
                                    $events = $parser->getEvents();
                                } else {
                                    $events = ['error' => 'Pustaka icalparser belum terpasang'];
                                }
                            } else {
                                $events = ['error' => 'Gagal mengambil data dari URL'];
                            }
                        } catch (\Exception $e) {
                            $events = ['error' => $e->getMessage()];
                        }
                        return view('filament.modals.ical-preview', [
                            'icalUrl' => $icalUrl,
                            'events'  => $events,
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index'  => Pages\ListIcalLinks::route('/'),
            'create' => Pages\CreateIcalLink::route('/create'),
            'edit'   => Pages\EditIcalLink::route('/{record}/edit'),
        ];
    }

    public static function isGloballySearchable(): bool
    {
        return false;
    }
}
