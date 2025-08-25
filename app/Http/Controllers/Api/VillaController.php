<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Villa;
use Carbon\CarbonPeriod;
use App\Models\IcalEvent;
use App\Models\VillaUnit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewBookingNotification;
use Illuminate\Support\Facades\DB;

class VillaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->hasRole('admin')) {
            $villas = Villa::with(['media', 'units', 'icalEvents'])->get();
        } else {
            $villas = $user->villas()->with(['media', 'units', 'icalEvents'])->get();
        }

        return response()->json([
            'message' => 'Villas retrieved successfully',
            'authenticated_user' => $user->only(['id', 'name', 'email']),
            'data' => $villas,
        ]);
    }


    public function show($id)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Pastikan villa memang ada
        $globalVilla = Villa::find($id);
        if (! $globalVilla) {
            return response()->json(['message' => 'Villa not found'], 404);
        }

        // Ambil dengan relasi sesuai peran
        if ($user->hasRole('admin')) {
            $villa = Villa::with(['media', 'units', 'icalEvents'])->find($id);
        } elseif ($user->hasRole('user')) {
            // Ambil dari relasi villa_user (hanya villa yang terkait dengan user)
            $villa = $user->villas()->with(['media', 'units', 'icalEvents'])->where('villas.id', $id)->first();

            if (! $villa) {
                return response()->json(['message' => 'Forbidden: You do not manage this villa.'], 403);
            }
        } else {
            // Untuk role lain, default ambil dari relasi user->villas juga (ubah sesuai kebutuhan)
            $villa = $user->villas()->with(['media', 'units', 'icalEvents'])->where('villas.id', $id)->first();

            if (! $villa) {
                return response()->json(['message' => 'Forbidden: You do not have access to this villa.'], 403);
            }
        }

        return response()->json([
            'message' => 'Villa retrieved successfully',
            'authenticated_user' => $user->only(['id', 'name', 'email']),
            'data' => $villa,
        ]);
    }

    public function getOccupancy(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|between:1,12',
        ]);

        $year = (int) $request->input('year');
        $month = (int) $request->input('month');

        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();

        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Ambil unit sesuai role
        if ($user->hasRole('admin')) {
            $units = VillaUnit::with('villa')->get();
        } else {
            $userVillaIds = $user->villas->pluck('id')->toArray();
            $units = VillaUnit::with('villa')->whereIn('villa_id', $userVillaIds)->get();
        }

        $unitIds = $units->pluck('id')->toArray();
        $totalUnits = count($unitIds);
        $daysInMonth = $startOfMonth->daysInMonth;
        $totalAvailableNights = $totalUnits * $daysInMonth;

        if ($totalUnits === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No villa units found.'
            ], 404);
        }

        // Ambil semua events yang overlap bulan ini
        $events = IcalEvent::where('start_date', '<=', $endOfMonth)
            ->where('end_date', '>=', $startOfMonth)
            ->get();

        // Siapkan map untuk menghitung booked nights per unit
        $bookedNightsPerUnit = array_fill_keys($unitIds, 0);

        foreach ($events as $event) {
            if (empty($event->villa_unit_id) || ! in_array($event->villa_unit_id, $unitIds)) {
                continue;
            }

            $eventStart = Carbon::parse($event->start_date);
            $eventEnd = Carbon::parse($event->end_date);

            $overlapStart = $eventStart->greaterThan($startOfMonth) ? $eventStart : $startOfMonth;
            $overlapEnd = $eventEnd->lessThan($endOfMonth) ? $eventEnd : $endOfMonth;

            $nights = CarbonPeriod::create($overlapStart->startOfDay(), $overlapEnd->startOfDay())->count() - 1;
            $nights = max(0, (int) $nights);

            $bookedNightsPerUnit[$event->villa_unit_id] += min($nights, $daysInMonth);
        }

        $totalAvailableRoomNights = 0;
        $fullyBookedUnits = 0;
        $unitsWithSomeAvailability = 0;
        $perUnitSummary = [];
        $totalRevenueIdr = 0;
        $totalPotentialRevenueIdr = 0;

        foreach ($units as $unit) {
            $unitId = $unit->id;
            $villa = $unit->villa;
            $pricePerDay = $villa ? ceil((int) $villa->price_idr / 30) : 0;

            // Ambil semua event untuk unit ini di bulan itu
            $unitEvents = $events->where('villa_unit_id', $unitId);

            $unitRevenueIdr = 0;
            $bookedNights = 0;

            foreach ($unitEvents as $event) {
                $eventStart = Carbon::parse($event->start_date)->startOfDay();
                $eventEnd = Carbon::parse($event->end_date)->startOfDay();

                // Hitung overlap dengan bulan ini
                $overlapStart = $eventStart->greaterThan($startOfMonth) ? $eventStart : $startOfMonth;
                $overlapEnd = $eventEnd->lessThan($endOfMonth) ? $eventEnd : $endOfMonth;

                $daysBooked = CarbonPeriod::create($overlapStart, $overlapEnd)->count() - 1;
                $daysBooked = max(0, $daysBooked);

                $bookedNights += $daysBooked;
                $unitRevenueIdr += ceil($daysBooked * $pricePerDay);
            }

            $bookedNights = min($bookedNights, $daysInMonth);
            $availableNights = max(0, $daysInMonth - $bookedNights);

            if ($availableNights === 0) {
                $fullyBookedUnits++;
            } else {
                $unitsWithSomeAvailability++;
            }

            $totalAvailableRoomNights += $availableNights;
            $totalRevenueIdr += $unitRevenueIdr;
            $totalPotentialRevenueIdr += ceil($daysInMonth * $pricePerDay);

            $perUnitSummary[] = [
                'unit_id'      => $unitId,
                'villa_id'     => $unit->villa_id,
                'villa_name'   => $villa ? $villa->name : null,
                'unit_name'    => $unit->name,
                'price_idr'    => (int) ($villa ? $villa->price_idr : 0),
                'price_per_day' => $pricePerDay,
                'booked_nights' => $bookedNights,
                'available_nights' => $availableNights,
                'revenue_idr'  => $unitRevenueIdr,
            ];
        }

        $occupancyRate = $totalAvailableNights > 0
            ? round((($totalAvailableNights - $totalAvailableRoomNights) / $totalAvailableNights) * 100, 2)
            : 0;

        return response()->json([
            'success' => true,
            'year' => $year,
            'month' => $month,
            'total_units' => $totalUnits,
            'days_in_month' => $daysInMonth,
            'total_available_nights' => $totalAvailableNights,
            'total_occupied_nights' => $totalAvailableNights - $totalAvailableRoomNights,
            'occupancy_rate_percent' => $occupancyRate,
            'total_available_room_nights' => $totalAvailableRoomNights,
            'average_available_per_day' => ceil($totalAvailableRoomNights / $daysInMonth),
            'fully_booked_units_count' => $fullyBookedUnits,
            'available_units_month' => $unitsWithSomeAvailability,
            'total_revenue_idr' => ceil($totalRevenueIdr),
            'total_potential_revenue_idr' => ceil($totalPotentialRevenueIdr),
            'per_unit_summary' => collect($perUnitSummary)->map(function ($item) {
                $item['price_per_day'] = ceil($item['price_per_day']);
                $item['revenue_idr'] = ceil($item['revenue_idr']);
                return $item;
            }),
        ]);
    }

    public function notification(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $villaId = $request->input('villa_id');
        $today = Carbon::today();

        // Ambil semua user_id yang punya akses ke villa ini (termasuk admin jika ada relasi)
        $accessUserIds = DB::table('villa_user')
            ->where('villa_id', $villaId)
            ->pluck('user_id')
            ->toArray();

        // Ambil semua unit di villa ini
        $villaUnits = VillaUnit::where('villa_id', $villaId)->get();
        $unitIds = $villaUnits->pluck('id')->toArray();

        // Ambil event yang dibuat hari ini untuk unit di villa ini
        $latestEvents = IcalEvent::whereIn('villa_unit_id', $unitIds)
            ->whereDate('created_at', $today)
            ->orderByDesc('created_at')
            ->get();

        if ($latestEvents->count() > 0) {
            // 1. Ambil semua admin
            $admins = \App\Models\User::whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })->get();

            // 2. Ambil semua user yang terkait dengan villa ini (berdasarkan relasi villa_user)
            $users = \App\Models\User::whereIn('id', $accessUserIds)->get();

            // 3. Gabungkan semua user dan pastikan unik
            $usersToNotify = $admins->merge($users)->unique('id');

            // 4. Kirim notifikasi ke semua user yang relevan
            Notification::send($usersToNotify, new NewBookingNotification($latestEvents->first()));
        }

        $notification = [
            'message' => $latestEvents->count() > 0 ? 'Ada booking baru!' : 'Tidak ada booking baru.',
            'new_events_count' => $latestEvents->count(),
            'new_events' => $latestEvents->values(),
        ];

        return response()->json([
            'success' => true,
            'notification' => $notification,
        ]);
    }
}
