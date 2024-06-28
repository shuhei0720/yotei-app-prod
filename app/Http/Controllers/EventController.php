<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function store(Request $request) {
        $request->validate([
            'team_id' => 'required|exists:teams,id',
            'name' => 'required|string|max:255',
            'start_datetime' => 'required|date_format:Y-m-d\TH:i',
            'end_datetime' => 'nullable|date_format:Y-m-d\TH:i|after_or_equal:start_datetime',
            'memo' => 'nullable|string',
        ]);

        Event::create([
            'team_id' => $request->team_id,
            'user_id' => Auth::id(),
            'name' => $request->name,
            'start_datetime' => $request->start_datetime,
            'end_datetime' => $request->end_datetime,
            'memo' => $request->memo,
        ]);

        return redirect()->route('teams.show', $request->team_id)->with('status', 'Event created successfully!');
    }

    public function names(Request $request) {
        $names = Event::select('name')->where('name', 'like', '%' . $request->q . '%')->distinct()->pluck('name');
        return response()->json($names);
    }

    public function update(Request $request, Event $event) {
        // 認可の確認
        if ($request->user()->cannot('update', $event)) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'start_datetime' => 'required|date_format:Y-m-d\TH:i',
            'end_datetime' => 'nullable|date_format:Y-m-d\TH:i|after_or_equal:start_datetime',
            'memo' => 'nullable|string',
        ]);

        $event->update([
            'name' => $request->name,
            'start_datetime' => $request->start_datetime,
            'end_datetime' => $request->end_datetime,
            'memo' => $request->memo,
        ]);

        return redirect()->route('teams.show', $event->team_id)->with('status', 'Event updated successfully!');
    }

    public function destroy(Event $event) {
        // 認可の確認
        if ($event->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $event->delete();

        return redirect()->route('teams.show', $event->team_id)->with('status', 'Event deleted successfully!');
    }
}