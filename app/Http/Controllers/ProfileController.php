<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        // 色と通知設定のバリデーションを追加
        $request->validate([
            'color' => 'required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'line_notifications' => 'nullable|string',
            'notification_time' => 'required|date_format:H:i',
        ]);

        $user = $request->user();
        $user->fill($validatedData);

        // 色と通知設定の更新を追加
        $user->color = $request->input('color');
        $user->line_notifications = filter_var($request->input('line_notifications', false), FILTER_VALIDATE_BOOLEAN);
        $user->notification_time = $request->input('notification_time');

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'username' => ['required', 'string'],
        ]);

        $user = $request->user();

        // ユーザー名が一致するか確認
        if ($request->input('username') !== $user->name) {
            return back()->withErrors([
                'username' => __('ユーザー名が一致しません。'),
            ]);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}