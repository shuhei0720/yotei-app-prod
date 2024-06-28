<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function store(Request $request) {
        $request->validate([
            'team_id' => 'required|exists:teams,id',
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        Event::create($request->all());

        return redirect()->route('teams.show', $request->team_id)->with('status', 'Event created successfully!');
    }

    public function names(Request $request) {
        $names = Event::select('name')
                      ->distinct()
                      ->where('name', 'like', "%{$request->query('q')}%")
                      ->limit(3)
                      ->pluck('name');

        return response()->json($names);
    }
}