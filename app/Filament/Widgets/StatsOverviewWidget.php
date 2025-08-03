<?php

namespace App\Filament\Widgets;

use App\Models\IcalEvent;
use App\Models\IcalLink;
use App\Models\User;
use App\Models\Villa;
use Filament\Widgets\StatsOverviewWidget as BaseWidget; // << PENTING: Ganti ini
use Filament\Widgets\StatsOverviewWidget\Stat; // << PENTING: Tambahkan ini
use Carbon\Carbon; // Pastikan ini ada

class StatsOverviewWidget extends BaseWidget // << PENTING: Pastikan extend BaseWidget
{
    // Hapus baris ini: protected static string $view = 'filament.widgets.stats-overview-widget';
    // Karena kita akan menggunakan metode getStats()

    protected int | string | array $columnSpan = 'full'; // Agar widget ini mengambil lebar penuh

    protected function getStats(): array
    {
        // Ambil data dari database Anda
        $totalVillas = Villa::count();
        $totalIcalLinks = IcalLink::count();
        $totalIcalEvents = IcalEvent::count();
        $totalUsers = User::count();

        // Hitung event yang sedang berjalan (hari ini) atau yang akan datang
        $upcomingEvents = IcalEvent::whereDate('start_date', '>=', now()->toDateString())->count();
        // Hitung event yang dibatalkan
        $cancelledEvents = IcalEvent::where('is_cancelled', true)->count();

        // Hitung Pendapatan Bulan Ini (Contoh Sederhana)
        // Ini mengasumsikan event 'Booked' atau 'CONFIRMED' membawa pendapatan
        // Dan harga diambil dari Villa terkait
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $revenueThisMonthIDR = IcalEvent::where('is_cancelled', false)
            ->where(function ($query) {
                $query->where('summary', 'Booked')
                      ->orWhere('status', 'CONFIRMED');
            })
            ->whereBetween('start_date', [$startOfMonth, $endOfMonth])
            ->with('icalLink.villa') // Eager load relasi
            ->get()
            ->sum(function ($event) {
                return $event->icalLink->villa->price_idr ?? 0;
            });

        $revenueThisMonthUSD = IcalEvent::where('is_cancelled', false)
            ->where(function ($query) {
                $query->where('summary', 'Booked')
                      ->orWhere('status', 'CONFIRMED');
            })
            ->whereBetween('start_date', [$startOfMonth, $endOfMonth])
            ->with('icalLink.villa')
            ->get()
            ->sum(function ($event) {
                return $event->icalLink->villa->price_usd ?? 0;
            });


        return [
            Stat::make('Total Villa', $totalVillas)
                ->description('Jumlah properti yang terdaftar')
                ->descriptionIcon('heroicon-o-home')
                ->color('info'),

            Stat::make('Total Link iCal', $totalIcalLinks)
                ->description('Jumlah link iCal yang disinkronkan')
                ->descriptionIcon('heroicon-o-link')
                ->color('success'),

            Stat::make('Total Book iCal', $totalIcalEvents)
                ->description('Total booking/blokir yang disinkronkan')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('warning'),

            Stat::make('Book Akan Datang', $upcomingEvents)
                ->description('Jumlah booking yang belum terjadi')
                ->descriptionIcon('heroicon-o-arrow-up')
                ->color('primary'),

            Stat::make('Book Dibatalkan', $cancelledEvents)
                ->description('Jumlah booking dengan status batal')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('Pendapatan Bulan Ini (IDR)', 'Rp ' . number_format($revenueThisMonthIDR, 0, ',', '.'))
                ->description('Estimasi pendapatan bulan berjalan')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success'),

            Stat::make('Pendapatan Bulan Ini (USD)', '$ ' . number_format($revenueThisMonthUSD, 2, '.', ','))
                ->description('Estimasi pendapatan bulan berjalan')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success'),

            Stat::make('Total Pengguna', $totalUsers)
                ->description('Jumlah akun di sistem')
                ->descriptionIcon('heroicon-o-users')
                ->color('secondary'),
        ];
    }
}
