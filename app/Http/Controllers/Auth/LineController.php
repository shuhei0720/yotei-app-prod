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
        return Socialite::driver('line')->redirect();
    }

    /**
     * Obtain the user information from LINE.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        try {
            $lineUser = Socialite::driver('line')->user();
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'LINEログインに失敗しました。');
        }

        // LINE IDでユーザーを検索
        $user = User::where('line_id', $lineUser->id)->first();

        if (!$user) {
            // メールアドレスでユーザーを検索
            $user = User::where('email', $lineUser->email)->first();

            if ($user) {
                // ユーザーが存在する場合、line_idを更新
                $user->line_id = $lineUser->id;
                $user->save();
            } else {
                // ユーザーが存在しない場合、新規ユーザーを作成
                $user = User::create([
                    'name' => $lineUser->name,
                    'email' => $lineUser->email ?? "{$lineUser->id}@line.com",
                    'password' => Hash::make(Str::random(24)), // 仮のパスワード
                    'line_id' => $lineUser->id,
                ]);
            }
        }

        // ログインさせる
        Auth::login($user, true);

        return redirect('/dashboard');
    }
}