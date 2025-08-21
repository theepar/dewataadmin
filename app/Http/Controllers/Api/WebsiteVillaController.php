<?php

namespace App\Http\Controllers\Api;

use App\Models\Villa;
use App\Http\Controllers\Controller;

class WebsiteVillaController extends Controller
{
    public function index()
    {
        return Villa::with(['media', 'units', 'events'])->get();
    }

    public function show($id)
    {
        $villa = Villa::with(['media', 'units', 'events'])->find($id);
        if (! $villa) {
            return response()->json(['message' => 'Villa not found'], 404);
        }
        return $villa;
    }
}
