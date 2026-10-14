<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    protected $table = 'agendas';

    protected $fillable = [
        'date',
        'title',
        'place_time',
    ];

    protected $casts = ['date' => 'date'];
}
