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
        // Validasi permintaan
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|between:1,12',
        ]);

        $year = $request->input('year');
        $month = $request->input('month');

        // Tentukan rentang tanggal untuk bulan yang dipilih
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->startOfDay();

        // Hitung total malam unit yang tersedia
        $totalUnits = VillaUnit::count();
        $daysInMonth = $startOfMonth->daysInMonth;
        $totalAvailableNights = $totalUnits * $daysInMonth;

        if ($totalUnits === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No villa units found.'
            ], 404);
        }

        // Hitung total malam unit yang terisi
        $totalOccupiedNights = 0;

        // Ambil semua event yang overlap dengan bulan yang dipilih
        $events = IcalEvent::where(function ($query) use ($startOfMonth, $endOfMonth) {
            $query->where('start_date', '<=', $endOfMonth)
                ->where('end_date', '>=', $startOfMonth);
        })->get();

        foreach ($events as $event) {
            $eventStartDate = Carbon::parse($event->start_date);
            $eventEndDate = Carbon::parse($event->end_date);

            // Tentukan rentang hari yang overlap antara event dan bulan
            $overlapStart = $eventStartDate->greaterThan($startOfMonth) ? $eventStartDate : $startOfMonth;
            $overlapEnd = $eventEndDate->lessThan($endOfMonth) ? $eventEndDate : $endOfMonth;

            $totalOccupiedNights += CarbonPeriod::create($overlapStart, $overlapEnd)->count() - 1;
        }

        // Hitung persentase okupansi
        $occupancyRate = ($totalOccupiedNights / $totalAvailableNights) * 100;

        // Kembalikan data dalam format JSON
        return response()->json([
            'success' => true,
            'year' => $year,
            'month' => $month,
            'total_available_nights' => $totalAvailableNights,
            'total_occupied_nights' => $totalOccupiedNights,
            'occupancy_rate' => round($occupancyRate, 2)
        ]);
    }
}
