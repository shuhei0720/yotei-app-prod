<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\User;

if (!function_exists('sendLineNotification')) {
    function sendLineNotification($user)
    {
        $nickname = $user->name;
        $tomorrow = Carbon::now()->addDay()->format('n月j日'); // 明日の日付をフォーマット（0を省く）
        $message = "{$nickname}さん\n";
        $message .= "こんばんは！予定アプリです✨\n";
        $message .= "明日({$tomorrow})の予定を連絡します🌙😉\n\n";

        $teams = $user->teams;
        $hasEvents = false; // 予定があるかどうかのフラグ

        foreach ($teams as $team) {
            $teamMessage = "{$team->name}🏠\n\n";
            $events = $team->events->where('start_datetime', '>=', Carbon::now()->addDay()->startOfDay())
                                  ->where('start_datetime', '<', Carbon::now()->addDays(2)->startOfDay());

            $index = 1;
            foreach ($events as $event) {
                $creatorName = $event->user->name; // 予定作成者の名前を取得
                if ($event->all_day) {
                    $timeDisplay = '(終日)';
                } else {
                    $startDatetime = Carbon::parse($event->start_datetime)->format('H:i');
                    $timeDisplay = "({$startDatetime})";
                }
                $teamMessage .= " ・{$creatorName}: {$event->name} {$timeDisplay}\n";
                $index++;
            }

            if ($events->isNotEmpty()) {
                $hasEvents = true; // 予定があることをフラグに設定
                $message .= $teamMessage . "\n";
            }
        }

        $message .= "本日もお疲れ様でした🌙😁✨";

        if ($hasEvents && !empty($message)) {
            $lineAccessToken = env('LINE_CHANNEL_ACCESS_TOKEN');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $lineAccessToken,
                'Content-Type' => 'application/json',
            ])->post('https://api.line.me/v2/bot/message/push', [
                'to' => $user->line_id,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $message
                    ]
                ],
            ]);

            if ($response->failed()) {
                // エラーハンドリング
            }
        }
    }
}

// SendLineNotification コマンドの定義
Artisan::command('send:linenotification', function () {
    $users = User::where('line_notifications', true)->get();

    foreach ($users as $user) {
        try {
            $notificationTime = Carbon::createFromFormat('H:i:s', $user->notification_time);
            $currentTime = Carbon::now()->format('H:i');

            if ($currentTime === $notificationTime->format('H:i')) {
                sendLineNotification($user);
            }
        } catch (\Exception $e) {
            // エラーハンドリング
        }
    }
})->purpose('Send LINE notifications to users at their scheduled time')->everyMinute();

// 他のArtisanコマンドの定義
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();