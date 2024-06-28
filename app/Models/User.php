<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'color', // colorカラムをfillableに追加
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function boot() {
        parent::boot();

        static::creating(function ($user) {
            $user->color = $user->generateColor(); // ユーザー作成時に色を生成
        });
    }

    public function generateColor() {
        $colors = ['#FF5733', '#33FF57', '#3357FF', '#F333FF', '#FF33A8', '#FFD700', '#FF4500', '#ADFF2F', '#7FFF00', '#7B68EE'];
        return $colors[array_rand($colors)];
    }
}