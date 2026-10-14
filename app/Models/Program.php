<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $casts = [
        'skills' => 'array',
        'jobs' => 'array',
        'facilities' => 'array',
    ];
}
