<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Villa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $query = \App\Models\IcalEvent::with('icalLink.villa.media');
        if ($user->hasRole('pegawai')) {
            $managedVillaIds = $user->villas->pluck('id');
            $query->whereHas('icalLink.villa', function ($q) use ($managedVillaIds) {
                $q->whereIn('id', $managedVillaIds);
            });
        }

        $icalEvents = $query->get();

        return response()->json([
            'message' => 'iCal events retrieved successfully',
            'data'    => $icalEvents->map(function ($event) {
                $villa = $event->icalLink->villa ?? null;
                return [
                    'id'             => $event->id,
                    'ical_link_id'   => $event->ical_link_id,
                    'uid'            => $event->uid,
                    'summary'        => $event->summary,
                    'description'    => $event->description,
                    'start_date'     => $event->start_date,
                    'end_date'       => $event->end_date,
                    'status'         => $event->status,
                    'guest_name'     => $event->guest_name,
                    'reservation_id' => $event->reservation_id,
                    'property_name'  => $event->property_name,
                    'jumlah_orang'   => $event->jumlah_orang,
                    'durasi'         => $event->durasi,
                    'is_cancelled'   => (bool) $event->is_cancelled,
                    'created_at'     => $event->created_at,
                    'updated_at'     => $event->updated_at,
                    'villa'          => [
                        'id'     => $villa->id ?? null,
                        'name'   => $villa->name ?? null,
                        'images' => $villa && $villa->media
                        ? $villa->media->map(fn($media) => asset('storage/' . $media->file_path))->values()
                        : [],
                    ],
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

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $villa = Villa::with('media')->find($id);

        if (! $villa) {
            return response()->json(['message' => 'Villa not found'], 404);
        }

        if ($user->hasRole('pegawai')) {
            // Pegawai hanya bisa lihat villa yang dia kelola
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
                'created_at'       => $villa->created_at->format('Y-m-d H:i:s'),
                'updated_at'       => $villa->updated_at->format('Y-m-d H:i:s'),
                'images'           => $villa->media->map(fn($media) => asset('storage/' . $media->file_path)),
            ],
        ]);
    }
}
