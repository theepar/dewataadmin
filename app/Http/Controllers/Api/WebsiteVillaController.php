<?php

namespace App\Http\Controllers\Api;

use App\Models\Villa;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
        $outputRaw = Artisan::output();

        // Parse output menjadi array
        $outputLines = explode("\n", trim($outputRaw));
        $output = [];
        foreach ($outputLines as $line) {
            $line = trim($line);
            if ($line) {
                $json = json_decode($line, true);
                if ($json) {
                    $output[] = $json;
                }
            }
        }

        $eventsQuery = \App\Models\IcalEvent::query();
        if ($unitId) {
            $eventsQuery->where('villa_unit_id', $unitId);
        }
        $allEvents = $eventsQuery->get();

        return response()->json([
            'success' => $exitCode === 0,
            'output' => $output, // array per unit, lengkap dengan waktu
            'ical_events' => $allEvents,
            'synced_at' => Carbon::now('Asia/Makassar')->toDateTimeString() . ' WITA',
        ]);
    }
}
