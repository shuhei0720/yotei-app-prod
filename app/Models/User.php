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
        'line_id',
        'color',
        'line_notifications',
        'notification_time',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'line_notifications' => 'boolean',
    ];

    protected static function boot() {
        parent::boot();

        static::creating(function ($user) {
            $user->color = $user->generateColor();
            if (is_null($user->line_notifications)) {
                $user->line_notifications = true;
            }
            if (is_null($user->notification_time)) {
                $user->notification_time = '20:00:00';
            }
        });
    }

    public function generateColor() {
        return $this->generateLightColor();
    }

    private function generateLightColor() {
        $red = rand(100, 255);
        $green = rand(100, 255);
        $blue = rand(100, 255);
        return sprintf("#%02X%02X%02X", $red, $green, $blue);
    }

    public function teams() {
        return $this->belongsToMany(Team::class, 'memberships');
    }
}