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
use Filament\Tables\Actions\Action;
use App\Models\IcalEvent;
use Illuminate\Support\Carbon;
use Filament\Notifications\Notification;

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
                Action::make('sync_ical')
                    ->label('Sync iCal')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function ($record, $livewire) {
                        $icalUrl = $record->ical_url;
                        $ics = @file_get_contents($icalUrl);
                        if (!$ics) {
                            Notification::make()
                                ->title('Gagal mengambil data dari URL')
                                ->danger()
                                ->send();
                            return;
                        }
                        if (!class_exists('om\\IcalParser')) {
                            Notification::make()
                                ->title('Pustaka icalparser belum terpasang')
                                ->danger()
                                ->send();
                            return;
                        }
                        $parser = new \om\IcalParser();
                        $parser->parseString($ics);
                        $events = $parser->getEvents();
                        $updated = 0;
                        $inserted = 0;
                        foreach ($events as $ev) {
                            $uid = $ev['UID'] ?? null;
                            $summary = $ev['SUMMARY'] ?? null;
                            $description = $ev['DESCRIPTION'] ?? null;
                            $start = isset($ev['DTSTART']) ? Carbon::parse($ev['DTSTART']) : null;
                            $end = isset($ev['DTEND']) ? Carbon::parse($ev['DTEND']) : null;
                            $status = $ev['STATUS'] ?? null;
                            $propertyName = $ev['LOCATION'] ?? null;
                            $guestName = $reservationId = $guestCount = null;
                            if (!empty($description)) {
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
                            $where = [
                                'ical_link_id' => $record->id,
                                'uid' => $uid,
                            ];
                            $data = [
                                'summary' => $summary,
                                'description' => $description,
                                'start_date' => $start,
                                'end_date' => $end,
                                'status' => $status,
                                'guest_name' => $guestName,
                                'reservation_id' => $reservationId,
                                'property_name' => $propertyName,
                                'jumlah_orang' => $guestCount,
                                'durasi' => $durasi,
                                'is_cancelled' => $isCancelled,
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
                                IcalEvent::create(array_merge($where, $data));
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
                        $events = [];
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
                            'events' => $events,
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
