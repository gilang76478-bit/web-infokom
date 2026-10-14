<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpdbApplicant extends Model
{
    protected $fillable = [
        'registration_number',
        'full_name',
        'phone',
        'origin_school',
        'program',
        'status',
    ];
}