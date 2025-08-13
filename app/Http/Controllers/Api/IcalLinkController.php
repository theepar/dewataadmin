<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Villa;
use App\Models\IcalLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IcalLinkController extends Controller
{
    /**
     * Get all iCal links (filtered by user access).
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

        if ($user->hasRole('admin')) {
            $icalLinks = IcalLink::with(['villa.media', 'user', 'events'])->get();
        } else {
            $villaIds = $user->villas->pluck('id');
            $icalLinks = IcalLink::with(['villa.media', 'user', 'events'])
                ->whereIn('villa_id', $villaIds)
                ->get();
        }

        return response()->json([
            'message' => 'iCal links retrieved successfully',
            'data'    => $icalLinks->map(function ($icalLink) {
                return [
                    'id'              => $icalLink->id,
                    'name'            => $icalLink->name,
                    'ical_url'        => $icalLink->ical_url,
                    'last_synced_at'  => $icalLink->last_synced_at ? $icalLink->last_synced_at->format('Y-m-d H:i:s') : null,
                    'created_by_user' => [
                        'id'   => $icalLink->user->id ?? null,
                        'name' => $icalLink->user->name ?? null,
                    ],
                    'related_villa'   => [
                        'id'     => $icalLink->villa->id ?? null,
                        'name'   => $icalLink->villa->name ?? null,
                        'images' => $icalLink->villa && $icalLink->villa->media
                            ? $icalLink->villa->media->map(fn($media) => asset('villa-images/' . $media->file_name))
                            : [],
                    ],
                    'events' => $icalLink->events->map(function ($event) {
                        return [
                            'id'             => $event->id,
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
                        ];
                    }),
                ];
            }),
        ]);
    }

    /**
     * Get a single iCal link by ID.
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

        $icalLink = IcalLink::with(['user', 'villa.media', 'events'])->find($id);

        if (! $icalLink) {
            return response()->json(['message' => 'iCal Link not found'], 404);
        }

        if ($user->hasRole('pegawai')) {
            $managedVillaIds = $user->villas->pluck('id');
            if (! in_array($icalLink->villa_id, $managedVillaIds->toArray())) {
                return response()->json(['message' => 'Forbidden: You do not manage this villa.'], 403);
            }
        }

        return response()->json([
            'message' => 'iCal link retrieved successfully',
            'data'    => [
                'id'              => $icalLink->id,
                'name'            => $icalLink->name,
                'ical_url'        => $icalLink->ical_url,
                'last_synced_at'  => $icalLink->last_synced_at ? $icalLink->last_synced_at->format('Y-m-d H:i:s') : null,
                'created_by_user' => [
                    'id'   => $icalLink->user->id ?? null,
                    'name' => $icalLink->user->name ?? null,
                ],
                'related_villa'   => [
                    'id'     => $icalLink->villa->id ?? null,
                    'name'   => $icalLink->villa->name ?? null,
                    'images' => $icalLink->villa && $icalLink->villa->media
                        ? $icalLink->villa->media->map(fn($media) => asset('villa-images/' . $media->file_name))
                        : [],
                ],
                'events' => $icalLink->events->map(function ($event) {
                    return [
                        'id'             => $event->id,
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
                    ];
                }),
            ],
        ]);
    }
}
