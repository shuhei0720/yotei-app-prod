<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model {
    use HasFactory;

    protected $fillable = ['team_id', 'name', 'start_date', 'end_date'];

    public function team() {
        return $this->belongsTo(Team::class);
    }
}