<?php
namespace App\Filament\Widgets;

use Carbon\Carbon;                                      // << PENTING: Ganti ini
use Filament\Widgets\StatsOverviewWidget as BaseWidget; // << PENTING: Tambahkan ini
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Villa', \App\Models\VillaUnit::count())
                ->description('Jumlah unit yang terdaftar')
                ->descriptionIcon('heroicon-o-home')
                ->color('info'),

            Stat::make('Total iCal Link', \App\Models\IcalLink::count())
                ->description('Jumlah link iCal yang disinkronkan')
                ->descriptionIcon('heroicon-o-link')
                ->color('success'),

            Stat::make('Total Event', \App\Models\IcalEvent::count())
                ->description('Total booking/blokir yang disinkronkan')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('warning'),

            Stat::make('Total User', \App\Models\User::count())
                ->description('Jumlah akun di sistem')
                ->descriptionIcon('heroicon-o-users')
                ->color('secondary'),
        ];
    }
}
