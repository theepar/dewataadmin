<?php
namespace App\Http\Middleware;

use App\Models\WebsiteApiKey;
use Closure;

class VerifyWebsiteApiKey
{
    public function handle($request, Closure $next)
    {
        $apiKey = $request->header('X-API-KEY');
        if (! WebsiteApiKey::where('api_key', $apiKey)->exists()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        return $next($request);
    }
}
