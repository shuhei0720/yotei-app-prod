<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request) {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'content' => 'required|string',
        ]);

        Comment::create([
            'event_id' => $request->event_id,
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        return redirect()->back()->with('status', 'コメントが追加されました！');
    }
}