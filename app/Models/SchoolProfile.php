<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolProfile extends Model
{
    protected $fillable = [
        'school_name',
        'school_code',
        'timezone',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
