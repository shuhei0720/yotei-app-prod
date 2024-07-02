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
        'color',
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
            $user->color = $user->generateColor();
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