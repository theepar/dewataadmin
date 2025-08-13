<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminOnly
{
    public function handle($request, Closure $next)
    {
        if (! Auth::check() || ! Auth::user()->hasRole('admin')) {
            Auth::logout();
            return redirect('/')->with('error', 'Hanya admin yang bisa login.');
        }
        return $next($request);
    }
}
