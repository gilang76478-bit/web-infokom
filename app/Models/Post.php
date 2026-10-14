<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'category',
        'title',
        'excerpt',
        'published_at',
        'art',
    ];

    protected $casts = ['published_at' => 'date'];
}
