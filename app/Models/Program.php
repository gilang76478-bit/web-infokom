<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $fillable = [
        'short',
        'icon',
        'name',
        'description',
        'skills',
        'jobs',
        'facilities',
    ];

    protected $casts = [
        'skills' => 'array',
        'jobs' => 'array',
        'facilities' => 'array',
    ];
}
