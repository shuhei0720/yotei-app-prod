<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class LineController extends Controller
{
    /**
     * Redirect the user to the LINE authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        $state = base64_encode(json_encode([
            'return_to' => url()->previous(),
            'intended' => '/dashboard'
        ]));
        \Log::debug('Redirecting to LINE with state: ' . $state);
        return Socialite::driver('line')->with(['state' => $state])->redirect();
    }

    /**
     * Obtain the user information from LINE.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        try {
            $lineUser = Socialite::driver('line')->stateless()->user();
            \Log::debug('LINE user retrieved: ' . $lineUser->id);
        } catch (\Exception $e) {
            \Log::error('Error retrieving LINE user: ' . $e->getMessage());
            return redirect('/')->with('error', 'LINEログインに失敗しました。');
        }

        $user = User::where('line_id', $lineUser->id)->first();

        if (!$user) {
            $user = User::where('email', $lineUser->email)->first();

            if ($user) {
                $user->line_id = $lineUser->id;
                $user->save();
                \Log::debug('Existing user updated: ' . $user->id);
            } else {
                $user = User::create([
                    'name' => $lineUser->name,
                    'email' => $lineUser->email ?? "{$lineUser->id}@line.com",
                    'password' => Hash::make(Str::random(24)),
                    'line_id' => $lineUser->id,
                ]);
                \Log::debug('New user created: ' . $user->id);
            }
        }

        Auth::login($user, true);
        \Log::debug('User logged in: ' . $user->id);

        $state = json_decode(base64_decode(request()->input('state')), true);
        \Log::debug('Decoded state: ' . print_r($state, true));
        $returnTo = $state['return_to'] ?? '/dashboard';
        $intended = $state['intended'] ?? '/dashboard';

        return redirect($returnTo . '#callback=' . urlencode($intended));
    }
}