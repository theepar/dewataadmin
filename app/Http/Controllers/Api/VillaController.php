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
            // Admin melihat semua villa
            $villas = Villa::all();
        } else {
            // Pegawai hanya melihat villa yang mereka kelola
            $villas = $user->villas()->get();
        }

        return response()->json([
            'message' => 'Villas retrieved successfully',
            'data'    => $villas->map(function ($villa) {
                return [
                    'id'               => $villa->id,
                    'name'             => $villa->name,
                    'ownership_status' => $villa->ownership_status,
                    'price_idr'        => $villa->price_idr,
                    'price_usd'        => $villa->price_usd,
                    'description'      => $villa->description,
                    'created_at'       => $villa->created_at->format('Y-m-d H:i:s'),
                    'updated_at'       => $villa->updated_at->format('Y-m-d H:i:s'),
                    // Hapus semua kolom terkait media library
                    // 'main_image_url' => null,
                    // 'gallery_images_urls' => [],
                    // 'video_url' => null,
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

        $villa = Villa::find($id);

        if (! $villa) {
            return response()->json(['message' => 'Villa not found'], 404);
        }

        // Otorisasi: Pegawai hanya bisa melihat villa yang mereka kelola
        if ($user->hasRole('pegawai')) {
            if (! $user->villas->contains($villa)) {
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
                'price_usd'        => $villa->price_usd,
                'description'      => $villa->description,
                'created_at'       => $villa->created_at->format('Y-m-d H:i:s'),
                'updated_at'       => $villa->updated_at->format('Y-m-d H:i:s'),
                // Hapus semua kolom terkait media library
                // 'main_image_url' => null,
                // 'gallery_images_urls' => [],
                // 'video_url' => null,
            ],
        ]);
    }

    // Untuk API, biasanya tidak ada store/update/delete villa, karena itu dilakukan via dashboard admin.
    // Jika perlu, Anda bisa menambahkannya di sini dengan otorisasi ketat (hanya admin).
}