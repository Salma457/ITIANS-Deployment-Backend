<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItianProject extends Model
{
    protected $fillable = [
        'itian_profile_id',
        'project_title',
        'description',
        'project_link'
    ];
}

