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
        $output = Artisan::output();

        $eventsQuery = \App\Models\IcalEvent::query();
        if ($unitId) {
            $eventsQuery->where('villa_unit_id', $unitId);
        }
        $allEvents = $eventsQuery->get()->map(function ($event) {
            return [
                'id' => $event->id,
                'villa_unit_id' => $event->villa_unit_id,
                'uid' => $event->uid,
                'summary' => $event->summary,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
            ];
        });

        return response()->json([
            'success' => $exitCode === 0,
            'output' => $output, // sudah berisi detail per unit dari command
            'ical_events' => $allEvents,
            'synced_at' => Carbon::now('Asia/Makassar')->toDateTimeString() . ' WITA',
        ]);
    }
}
