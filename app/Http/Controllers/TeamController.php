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
        $team = Auth::user()->team;
        if ($team) {
            return redirect()->route('teams.show', $team->id);
        }
        return view('teams.index');
    }

    public function store(Request $request) {
        $user = Auth::user();

        if ($user->team) {
            return redirect()->route('teams.index')->with('error', '1つのチームしか作成または参加できません。');
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $team = Team::create(['name' => $request->name]);
        $user->team_id = $team->id;
        $user->save();

        return redirect()->route('teams.show', $team->id);
    }

    public function join(Request $request) {
        $user = Auth::user();

        if ($user->team) {
            return redirect()->route('teams.index')->with('error', '1つのチームしか作成または参加できません。');
        }

        $request->validate([
            'team_id' => 'required|exists:teams,id',
        ]);

        $user->team_id = $request->team_id;
        $user->save();

        return redirect()->route('teams.show', $request->team_id);
    }

    public function leave() {
        $user = Auth::user();
        $user->team_id = null;
        $user->save();

        return redirect()->route('dashboard')->with('status', 'チームを離脱しました。');
    }

    public function show(Team $team) {
        if (Auth::user()->team_id !== $team->id) {
            return redirect()->route('teams.show', Auth::user()->team_id);
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

        // 取得したイベントデータをログに出力
        Log::info('Events: ' . json_encode($events));

        return view('teams.show', compact('team', 'events'));
    }
}