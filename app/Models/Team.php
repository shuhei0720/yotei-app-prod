<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model {
    use HasFactory;

    protected $fillable = ['name'];

    public function members() {
        return $this->hasMany(Membership::class);
    }

    public function events() {
        return $this->hasMany(Event::class);
    }
}