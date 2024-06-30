<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function members() {
        return $this->belongsToMany(User::class, 'memberships');
    }

    public function events() {
        return $this->hasMany(Event::class);
    }
}