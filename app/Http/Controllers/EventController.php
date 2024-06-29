<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    public function store(Request $request) {
        $request->validate([
            'team_id' => 'required|exists:teams,id',
            'name' => 'required|string|max:255',
            'start_datetime' => 'required|date_format:Y-m-d\TH:i',
            'end_datetime' => 'nullable|date_format:Y-m-d\TH:i|after_or_equal:start_datetime',
            'memo' => 'nullable|string',
            'all_day' => 'nullable|boolean'
        ]);

        $allDay = $request->input('all_day', false); // all_dayが送信されない場合はfalseをデフォルトとする

        Log::debug('All Day Value:', ['all_day' => $allDay]);

        $startDatetime = $request->start_datetime;
        $endDatetime = $request->end_datetime;

        if ($allDay) {
            $startDatetime = substr($request->start_datetime, 0, 10) . ' 00:00:00';
            $endDatetime = substr($request->end_datetime, 0, 10) . ' 23:59:59';
        }

        Event::create([
            'team_id' => $request->team_id,
            'user_id' => Auth::id(),
            'name' => $request->name,
            'start_datetime' => $startDatetime,
            'end_datetime' => $endDatetime,
            'memo' => $request->memo,
            'all_day' => $allDay
        ]);

        return redirect()->route('teams.show', $request->team_id)->with('status', 'Event created successfully!');
    }

    public function names(Request $request) {
        $names = Event::select('name')->where('name', 'like', '%' . $request->q . '%')->distinct()->pluck('name');
        return response()->json($names);
    }
}