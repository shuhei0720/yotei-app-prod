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
                    'user' => $user->name, // 追加
                    'memo' => $event->memo,
                    'comments' => $event->comments->map(function ($comment) {
                        return [
                            'content' => $comment->content,
                            'user' => $comment->user->name,
                            'user_color' => $comment->user->color // 追加
                        ];
                    }),
                    'created_by' => $user->name, // 追加
                    'created_by_color' => $user->color // 追加
                ]
            ];
        })->toArray();

        // 取得したイベントデータをログに出力
        Log::info('Events: ' . json_encode($events));

        return view('teams.show', compact('team', 'events'));
    }
}