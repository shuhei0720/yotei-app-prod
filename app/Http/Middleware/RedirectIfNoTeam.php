<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfNoTeam
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && !Auth::user()->team_id) {
            return redirect()->route('teams.index');
        }

        return $next($request);
    }
}