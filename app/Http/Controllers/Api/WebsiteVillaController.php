<?php

namespace App\Http\Controllers\Api;

use App\Models\Villa;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

class WebsiteVillaController extends Controller
{
    public function index()
    {
        return Villa::with(['media', 'units', 'icalEvents'])->get();
    }

    public function show($id)
    {
        $villa = Villa::with(['media', 'units', 'icalEvents'])->find($id);
        if (! $villa) {
            return response()->json(['message' => 'Villa not found'], 404);
        }
        return $villa;
    }

    public function syncIcal(Request $request)
    {
        $unitId = $request->input('unit_id');
        $exitCode = Artisan::call('ical:sync', $unitId ? ['unit_id' => $unitId] : []);
        $output = Artisan::output();

        return response()->json([
            'success' => $exitCode === 0,
            'output' => $output,
        ]);
    }
}