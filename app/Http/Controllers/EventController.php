<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function store(Request $request) {
        try {
            $request->validate([
                'team_id' => 'required|exists:teams,id',
                'name' => 'required|string|max:255',
                'start_datetime' => 'required|date_format:Y-m-d\TH:i',
                'end_datetime' => 'nullable|date_format:Y-m-d\TH:i|after_or_equal:start_datetime',
                'memo' => 'nullable|string',
                'all_day' => [
                    'nullable',
                    Rule::in(['true', 'false', 1, 0, true, false]),
                ],
            ]);

            $allDay = filter_var($request->input('all_day', false), FILTER_VALIDATE_BOOLEAN);

            $startDatetime = $request->start_datetime;
            $endDatetime = $request->end_datetime;

            if ($allDay) {
                $startDatetime = substr($request->start_datetime, 0, 10) . ' 00:00:00';
                $endDatetime = substr($request->start_datetime, 0, 10) . ' 23:59:59';
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

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Event store error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id) {
        try {
            $request->validate([
                'team_id' => 'required|exists:teams,id',
                'name' => 'required|string|max:255',
                'start_datetime' => 'required|date_format:Y-m-d\TH:i',
                'end_datetime' => 'nullable|date_format:Y-m-d\TH:i|after_or_equal:start_datetime',
                'memo' => 'nullable|string',
                'all_day' => [
                    'nullable',
                    Rule::in(['true', 'false', 1, 0, true, false]),
                ],
            ]);

            $allDay = filter_var($request->input('all_day', false), FILTER_VALIDATE_BOOLEAN);

            $event = Event::findOrFail($id);

            $startDatetime = $request->start_datetime;
            $endDatetime = $request->end_datetime;

            if ($allDay) {
                $startDatetime = substr($request->start_datetime, 0, 10) . ' 00:00:00';
                $endDatetime = substr($request->start_datetime, 0, 10) . ' 23:59:59';
            }

            $event->update([
                'team_id' => $request->team_id,
                'name' => $request->name,
                'start_datetime' => $startDatetime,
                'end_datetime' => $endDatetime,
                'memo' => $request->memo,
                'all_day' => $allDay
            ]);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Event update error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id) {
        try {
            $event = Event::findOrFail($id);
            $event->delete();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Event delete error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function names(Request $request) {
        $names = Event::select('name')->where('name', 'like', '%' . $request->q . '%')->distinct()->pluck('name');
        return response()->json($names);
    }
}