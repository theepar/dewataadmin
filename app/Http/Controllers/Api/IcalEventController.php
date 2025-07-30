<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IcalEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class IcalEventController extends Controller
{
    /**
     * Get all iCal events (filtered by user access).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->hasRole('admin')) {
            // Admin melihat semua iCal events
            $icalEvents = IcalEvent::with('icalLink.villa')->get();
        } else {
            // Pegawai hanya melihat iCal events yang terkait dengan villa yang mereka kelola
            $managedVillaIds = $user->villas->pluck('id');

            $icalEvents = IcalEvent::whereHas('icalLink.villa', function ($query) use ($managedVillaIds) {
                $query->whereIn('id', $managedVillaIds);
            })->with('icalLink.villa')->get();
        }

        // Transformasi data untuk response API
        return response()->json([
            'message' => 'iCal events retrieved successfully',
            'data' => $icalEvents->map(function($event) {
                // Mengambil harga dari villa yang terkait
                $priceIdr = $event->icalLink->villa->price_idr ?? null;
                $priceUsd = $event->icalLink->villa->price_usd ?? null;

                return [
                    'id' => $event->id,
                    'uid' => $event->uid,
                    'summary' => $event->summary,
                    'description' => $event->description,
                    'start_date' => $event->start_date ? $event->start_date->format('Y-m-d H:i:s') : null,
                    'end_date' => $event->end_date ? $event->end_date->format('Y-m-d H:i:s') : null,
                    'status' => $event->status,
                    'guest_name' => $event->guest_name,
                    'reservation_id' => $event->reservation_id,
                    'is_cancelled' => (bool) $event->is_cancelled,
                    'ical_link_id' => $event->ical_link_id,
                    'villa' => [
                        'id' => $event->icalLink->villa->id ?? null,
                        'name' => $event->icalLink->villa->name ?? null,
                        'ownership_status' => $event->icalLink->villa->ownership_status ?? null,
                        'price_idr' => $priceIdr,
                        'price_usd' => $priceUsd,
                        // Jika Anda sudah mengimplementasikan Media Library untuk Villa, Anda bisa tambahkan URL gambar di sini
                        // 'main_image_url' => $event->icalLink->villa->getFirstMediaUrl('images'),
                    ],
                    'last_synced_at' => $event->icalLink->last_synced_at ? $event->icalLink->last_synced_at->format('Y-m-d H:i:s') : null,
                ];
            }),
        ]);
    }

    /**
     * Get a single iCal event by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $icalEvent = IcalEvent::with('icalLink.villa')->find($id);

        if (!$icalEvent) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        // Otorisasi: Pegawai hanya bisa melihat event yang terkait dengan villa yang mereka kelola
        if ($user->hasRole('pegawai')) {
            $managedVillaIds = $user->villas->pluck('id');
            if (!in_array($icalEvent->icalLink->villa->id, $managedVillaIds->toArray())) {
                return response()->json(['message' => 'Forbidden: You do not manage this villa.'], 403);
            }
        }

        return response()->json([
            'message' => 'iCal event retrieved successfully',
            'data' => [
                'id' => $icalEvent->id,
                'uid' => $icalEvent->uid,
                'summary' => $icalEvent->summary,
                'description' => $icalEvent->description,
                'start_date' => $icalEvent->start_date ? $icalEvent->start_date->format('Y-m-d H:i:s') : null,
                'end_date' => $icalEvent->end_date ? $icalEvent->end_date->format('Y-m-d H:i:s') : null,
                'status' => $icalEvent->status,
                'guest_name' => $icalEvent->guest_name,
                'reservation_id' => $icalEvent->reservation_id,
                'is_cancelled' => (bool) $icalEvent->is_cancelled,
                'ical_link_id' => $icalEvent->ical_link_id,
                'villa' => [
                    'id' => $icalEvent->icalLink->villa->id ?? null,
                    'name' => $icalEvent->icalLink->villa->name ?? null,
                    'ownership_status' => $icalEvent->icalLink->villa->ownership_status ?? null,
                    'price_idr' => $icalEvent->icalLink->villa->price_idr ?? null,
                    'price_usd' => $icalEvent->icalLink->villa->price_usd ?? null,
                ],
                'last_synced_at' => $icalEvent->icalLink->last_synced_at ? $icalEvent->icalLink->last_synced_at->format('Y-m-d H:i:s') : null,
            ],
        ]);
    }
}