<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrawlReport extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'updated_count' => 'integer',
        'created_count' => 'integer',
        'runtime' => 'datetime',
    ];
}
