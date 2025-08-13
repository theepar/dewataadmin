<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Villa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VillaController extends Controller
{
    /**
     * Get all villas (filtered by user access).
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
            // Ambil villa yang user kelola (hanya satu villa)
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
                    'images'           => $villa->media->map(function ($media) {
                        return asset('storage/' . $media->file_path);
                    }),
                ];
            }),
        ]);
    }

    /**
     * Get a single villa by ID.
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
            // Pegawai hanya bisa lihat villa yang dia kelola (hanya satu villa)
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
                'images'           => $villa->media->map(function ($media) {
                    return asset('storage/' . $media->file_path);
                }),
            ],
        ]);
    }

    /**
     * Get all villas for the website.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function websiteIndex(Request $request)
    {
        $villas = Villa::with('media')->get();

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
}
