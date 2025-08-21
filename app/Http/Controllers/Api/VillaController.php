<?php

namespace App\Http\Controllers\Api;

use App\Models\Villa;
use App\Models\IcalEvent;
use App\Models\VillaUnit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

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
        } elseif ($user->hasRole('pegawai')) {
            // Ambil dari relasi villa_user (hanya villa yang terkait dengan pegawai)
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

        $totalUnits = VillaUnit::count();
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
        $unitIds = VillaUnit::pluck('id')->toArray();
        $bookedNightsPerUnit = array_fill_keys($unitIds, 0);

        foreach ($events as $event) {
            // abaikan event tanpa villa_unit_id
            if (empty($event->villa_unit_id) || ! in_array($event->villa_unit_id, $unitIds)) {
                continue;
            }

            $eventStart = Carbon::parse($event->start_date);
            $eventEnd = Carbon::parse($event->end_date);

            $overlapStart = $eventStart->greaterThan($startOfMonth) ? $eventStart : $startOfMonth;
            $overlapEnd = $eventEnd->lessThan($endOfMonth) ? $eventEnd : $endOfMonth;

            // jumlah malam overlap (difference in days)
            $nights = CarbonPeriod::create($overlapStart->startOfDay(), $overlapEnd->startOfDay())->count() - 1;
            $nights = max(0, (int) $nights);

            // jangan lebih dari daysInMonth
            $bookedNightsPerUnit[$event->villa_unit_id] += min($nights, $daysInMonth);
        }

        // Hitung available nights per unit dan summary per-bulan
        $totalAvailableRoomNights = 0;
        $fullyBookedUnits = 0;
        $unitsWithSomeAvailability = 0;
        $perUnitSummary = [];

        foreach ($bookedNightsPerUnit as $unitId => $bookedNights) {
            $bookedNights = min($bookedNights, $daysInMonth);
            $availableNights = max(0, $daysInMonth - $bookedNights);

            if ($availableNights === 0) {
                $fullyBookedUnits++;
            } else {
                $unitsWithSomeAvailability++;
            }

            $totalAvailableRoomNights += $availableNights;

            $perUnitSummary[] = [
                'unit_id' => $unitId,
                'booked_nights' => $bookedNights,
                'available_nights' => $availableNights,
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
            'average_available_per_day' => round($totalAvailableRoomNights / $daysInMonth, 2),
            'fully_booked_units_count' => $fullyBookedUnits,
            'available_units_month' => $unitsWithSomeAvailability,
            'per_unit_summary' => $perUnitSummary,
        ]);
    }
}
