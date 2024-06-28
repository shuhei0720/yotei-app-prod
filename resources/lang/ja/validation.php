<?php

return [
    'required' => ':attribute は必須です。',
    'string' => ':attribute は文字列でなければなりません。',
    'max' => [
        'string' => ':attribute は :max 文字以内でなければなりません。',
    ],
    'exists' => '選択された :attribute は無効です。',
    'date_format' => ':attribute は「Y-m-d\TH:i」形式でなければなりません。',
    'after_or_equal' => ':attribute は :date 以降の日付でなければなりません。',

    'attributes' => [
        'name' => '名前',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'team_id' => 'チームID',
        'start_datetime' => '開始日時',
        'end_datetime' => '終了日時',
        'memo' => 'メモ',
        'content' => '内容',
    ],
];