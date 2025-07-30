<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IcalLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->hasRole('admin')) {
            // Admin melihat semua iCal links
            $icalLinks = IcalLink::with('user', 'villa')->get();
        } else {
            // Pegawai hanya melihat iCal links yang terkait dengan villa yang mereka kelola
            $managedVillaIds = $user->villas->pluck('id');
            $icalLinks = IcalLink::whereIn('villa_id', $managedVillaIds)
                                 ->with('user', 'villa')
                                 ->get();
        }

        return response()->json([
            'message' => 'iCal links retrieved successfully',
            'data' => $icalLinks->map(function($link) {
                return [
                    'id' => $link->id,
                    'name' => $link->name,
                    'ical_url' => $link->ical_url,
                    'last_synced_at' => $link->last_synced_at ? $link->last_synced_at->format('Y-m-d H:i:s') : null,
                    'created_by_user' => [
                        'id' => $link->user->id ?? null,
                        'name' => $link->user->name ?? null,
                    ],
                    'related_villa' => [
                        'id' => $link->villa->id ?? null,
                        'name' => $link->villa->name ?? null,
                    ],
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

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $icalLink = IcalLink::with('user', 'villa')->find($id);

        if (!$icalLink) {
            return response()->json(['message' => 'iCal Link not found'], 404);
        }

        // Otorisasi: Pegawai hanya bisa melihat link yang terkait dengan villa yang mereka kelola
        if ($user->hasRole('pegawai')) {
            $managedVillaIds = $user->villas->pluck('id');
            if (!in_array($icalLink->villa_id, $managedVillaIds->toArray())) {
                return response()->json(['message' => 'Forbidden: You do not manage this villa.'], 403);
            }
        }

        return response()->json([
            'message' => 'iCal link retrieved successfully',
            'data' => [
                'id' => $icalLink->id,
                'name' => $icalLink->name,
                'ical_url' => $icalLink->ical_url,
                'last_synced_at' => $icalLink->last_synced_at ? $icalLink->last_synced_at->format('Y-m-d H:i:s') : null,
                'created_by_user' => [
                    'id' => $icalLink->user->id ?? null,
                    'name' => $icalLink->user->name ?? null,
                ],
                'related_villa' => [
                    'id' => $icalLink->villa->id ?? null,
                    'name' => $icalLink->villa->name ?? null,
                ],
            ],
        ]);
    }
}