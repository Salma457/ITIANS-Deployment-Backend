<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployerRegistration extends Model
{
    //
    protected $fillable = [
        'user_id',
        'company_brief',
        'status',
        'reviewed_by_admin_id',
    ];
}
