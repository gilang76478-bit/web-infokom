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

    /** Inisial nama untuk avatar ketika foto belum tersedia. */
    public function getInitialsAttribute(): string
    {
        return \App\Support\Initials::from($this->name, 'A');
    }
}
