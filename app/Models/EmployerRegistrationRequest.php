<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployerRegistrationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_brief',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
