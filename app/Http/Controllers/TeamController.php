<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Membership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index() {
        return view('teams.index');
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $team = Team::create(['name' => $request->name]);

        Membership::create([
            'user_id' => Auth::id(),
            'team_id' => $team->id,
        ]);

        return redirect()->route('teams.show', $team);
    }

    public function join(Request $request) {
        $request->validate([
            'team_id' => 'required|exists:teams,id',
        ]);

        Membership::create([
            'user_id' => Auth::id(),
            'team_id' => $request->team_id,
        ]);

        $team = Team::find($request->team_id);
        return redirect()->route('teams.show', $team);
    }

    public function show(Team $team) {
        $events = $team->events;
        return view('teams.show', compact('team', 'events'));
    }
}