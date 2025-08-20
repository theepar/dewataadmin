<?php

namespace App\Http\Controllers\Api;

use App\Models\Villa;
use App\Models\IcalEvent;
use App\Models\VillaUnit; // <-- Tambahkan model ini
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // <-- Tambahkan Carbon untuk penanganan tanggal
use Carbon\CarbonPeriod; // <-- Tambahkan CarbonPeriod untuk iterasi tanggal

class VillaController extends Controller
{
    /**
     * Get all villas (filtered by user access).
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->hasRole('admin')) {
            $villas = Villa::with(['media', 'villaUnits'])->get();
        } else {
            $villas = $user->villas()->with(['media', 'villaUnits'])->get();
        }

        return response()->json([
            'message' => 'Villas retrieved successfully',
            'data' => $villas->map(function ($villa) {
                return [
                    'id'               => $villa->id,
                    'name'             => $villa->name,
                    'ownership_status' => $villa->ownership_status,
                    'price_idr'        => $villa->price_idr,
                    'description'      => $villa->description,
                    'guest'            => $villa->guest,
                    'created_at'       => $villa->created_at->setTimezone('Asia/Makassar')->format('Y-m-d H:i:s'),
                    'updated_at'       => $villa->updated_at->setTimezone('Asia/Makassar')->format('Y-m-d H:i:s'),
                    'images'           => $villa->media->map(fn($media) => asset('villa-images/' . $media->file_name)),
                    'units'            => $villa->villaUnits->map(function ($unit) {
                        return [
                            'id'             => $unit->id,
                            'name'           => $unit->name,
                            'description'    => $unit->description,
                            'price_idr'      => $unit->price_idr,
                            'ical_link'      => $unit->ical_link,
                            'last_synced_at' => $unit->last_synced_at ? $unit->last_synced_at->setTimezone('Asia/Makassar')->format('Y-m-d H:i:s') : null,
                            'events'         => IcalEvent::where('villa_unit_id', $unit->id)
                                ->orderBy('start_date', 'asc')
                                ->get()
                                ->map(function ($ev) {
                                    return [
                                        'id'             => $ev->id,
                                        'uid'            => $ev->uid,
                                        'summary'        => $ev->summary,
                                        'description'    => $ev->description,
                                        'start_date'     => $ev->start_date ? $ev->start_date->setTimezone('Asia/Makassar')->format('Y-m-d H:i') : null,
                                        'end_date'       => $ev->end_date ? $ev->end_date->setTimezone('Asia/Makassar')->format('Y-m-d H:i') : null,
                                        'status'         => $ev->status,
                                        'guest_name'     => $ev->guest_name,
                                        'reservation_id' => $ev->reservation_id,
                                        'property_name'  => $ev->property_name,
                                        'jumlah_orang'   => $ev->jumlah_orang,
                                        'durasi'         => $ev->durasi,
                                        'is_cancelled'   => $ev->is_cancelled,
                                        'created_at'     => $ev->created_at ? $ev->created_at->setTimezone('Asia/Makassar')->format('Y-m-d H:i:s') : null,
                                        'updated_at'     => $ev->updated_at ? $ev->updated_at->setTimezone('Asia/Makassar')->format('Y-m-d H:i:s') : null,
                                    ];
                                }),
                        ];
                    }),
                ];
            }),
        ]);
    }

    // ... (Fungsi show() Anda yang asli)
    /**
     * Get a single villa by ID.
     */
    public function show($id)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $villa = Villa::with(['media', 'villaUnits'])->find($id);

        if (! $villa) {
            return response()->json(['message' => 'Villa not found'], 404);
        }

        if ($user->hasRole('pegawai')) {
            if (! $user->villas->pluck('id')->contains($villa->id)) {
                return response()->json(['message' => 'Forbidden: You do not manage this villa.'], 403);
            }
        }

        return response()->json([
            'message' => 'Villa retrieved successfully',
            'data'    => [
                'id'               => $villa->id,
                'name'             => $villa->name,
                'ownership_status' => $villa->ownership_status,
                'price_idr'        => $villa->price_idr,
                'description'      => $villa->description,
                'guest'            => $villa->guest, // Tambahkan guest di sini
                'created_at'       => $villa->created_at->format('Y-m-d H:i:s'),
                'updated_at'       => $villa->updated_at->format('Y-m-d H:i:s'),
                'images'           => $villa->media->map(fn($media) => asset('villa-images/' . $media->file_name)),
                'units'            => $villa->villaUnits->map(function ($unit) {
                    return [
                        'id'             => $unit->id,
                        'name'           => $unit->name,
                        'description'    => $unit->description,
                        'price_idr'      => $unit->price_idr,
                        'ical_link'      => $unit->ical_link,
                        'last_synced_at' => $unit->last_synced_at ? $unit->last_synced_at->format('Y-m-d H:i:s') : null,
                        'events'         => IcalEvent::where('villa_unit_id', $unit->id)
                            ->orderBy('start_date', 'asc')
                            ->get()
                            ->map(function ($ev) {
                                return [
                                    'id'             => $ev->id,
                                    'uid'            => $ev->uid,
                                    'summary'        => $ev->summary,
                                    'description'    => $ev->description,
                                    'start_date'     => $ev->start_date ? $ev->start_date->setTimezone('Asia/Makassar')->format('Y-m-d H:i') : null,
                                    'end_date'       => $ev->end_date ? $ev->end_date->setTimezone('Asia/Makassar')->format('Y-m-d H:i') : null,
                                    'status'         => $ev->status,
                                    'guest_name'     => $ev->guest_name,
                                    'reservation_id' => $ev->reservation_id,
                                    'property_name'  => $ev->property_name,
                                    'jumlah_orang'   => $ev->jumlah_orang,
                                    'durasi'         => $ev->durasi,
                                    'is_cancelled'   => $ev->is_cancelled,
                                    'created_at'     => $ev->created_at ? $ev->created_at->setTimezone('Asia/Makassar')->format('Y-m-d H:i:s') : null,
                                    'updated_at'     => $ev->updated_at ? $ev->updated_at->setTimezone('Asia/Makassar')->format('Y-m-d H:i:s') : null,
                                ];
                            }),
                    ];
                }),
            ],
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
