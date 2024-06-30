<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TeamController extends Controller
{
    public function index() {
        $teams = Auth::user()->teams;
        return view('teams.index', compact('teams'));
    }

    public function store(Request $request) {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $team = Team::create(['name' => $request->name]);
        $user->teams()->attach($team->id);

        return redirect()->route('teams.show', $team->id);
    }

    public function join(Request $request) {
        $user = Auth::user();

        $request->validate([
            'team_id' => 'required|exists:teams,id',
        ]);

        $user->teams()->attach($request->team_id);

        return redirect()->route('teams.show', $request->team_id);
    }

    public function leave(Request $request) {
        $user = Auth::user();

        $request->validate([
            'team_id' => 'required|exists:teams,id',
        ]);

        $user->teams()->detach($request->team_id);

        return redirect()->route('dashboard')->with('status', 'チームを離脱しました。');
    }

    public function show(Team $team) {
        if (!Auth::user()->teams->contains($team->id)) {
            return redirect()->route('teams.index');
        }

        $events = Event::where('team_id', $team->id)->with('comments.user')->get()->map(function ($event) {
            $user = User::find($event->user_id);
            return [
                'id' => $event->id,
                'title' => $event->name,
                'start' => $event->start_datetime,
                'end' => $event->end_datetime,
                'extendedProps' => [
                    'color' => $user->color,
                    'user' => $user->name,
                    'memo' => $event->memo,
                    'comments' => $event->comments->map(function ($comment) {
                        return [
                            'content' => $comment->content,
                            'user' => $comment->user->name,
                            'user_color' => $comment->user->color
                        ];
                    }),
                    'created_by' => $user->name,
                    'created_by_color' => $user->color
                ]
            ];
        })->toArray();

        Log::info('Events: ' . json_encode($events));

        return view('teams.show', compact('team', 'events'));
    }
}