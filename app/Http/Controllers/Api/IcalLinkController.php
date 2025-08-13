<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Villa;
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
            $villas = Villa::with('media')->get();
        } else {
            // Pegawai hanya melihat villa yang dia kelola
            $villas = $user->villas()->with('media')->get();
        }

        return response()->json([
            'message' => 'Villas retrieved successfully',
            'data'    => $villas->map(function ($villa) {
                return [
                    'id'               => $villa->id,
                    'name'             => $villa->name,
                    'ownership_status' => $villa->ownership_status,
                    'price_idr'        => $villa->price_idr,
                    'description'      => $villa->description,
                    'created_at'       => $villa->created_at->format('Y-m-d H:i:s'),
                    'updated_at'       => $villa->updated_at->format('Y-m-d H:i:s'),
                    'images'           => $villa->media->map(fn($media) => asset('storage/' . $media->file_path)),
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

        $icalLink = \App\Models\IcalLink::with(['user', 'villa.media'])->find($id);

        if (! $icalLink) {
            return response()->json(['message' => 'iCal Link not found'], 404);
        }

        // Otorisasi: Pegawai hanya bisa melihat link yang terkait dengan villa yang mereka kelola
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
                    ? $icalLink->villa->media->map(fn($media) => asset('storage/' . $media->file_path))
                    : [],
                ],
            ],
        ]);
    }
}
