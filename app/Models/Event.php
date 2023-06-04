<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    protected $casts = [
        'attendee_count' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'live_date' => 'datetime',
    ];

    protected $guarded = [];

    public function getDateAttribute()
    {
        return $this->start_date->format('d/m/Y');
    }
}
