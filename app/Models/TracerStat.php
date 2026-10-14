<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TracerStat extends Model
{
    protected $fillable = [
        'label',
        'value',
        'color',
    ];
}
