<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JapanStep extends Model
{
    protected $fillable = [
        'title',
        'description',
        'duration',
    ];
}
