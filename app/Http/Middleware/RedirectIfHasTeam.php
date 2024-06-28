<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfHasTeam
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if ($user && $user->team_id) {
            return redirect()->route('teams.show', $user->team_id);
        }

        return $next($request);
    }
}