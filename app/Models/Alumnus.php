<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alumnus extends Model
{
    protected $table = 'alumni';

    protected $fillable = [
        'name',
        'role_desc',
        'quote',
    ];
}
