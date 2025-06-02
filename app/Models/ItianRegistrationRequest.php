<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItianRegistrationRequest extends Model
{
    //
    protected $fillable = [
        'user_id',
        'certificate',
        'status',
        'reviewed_by_admin_id',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedByAdmin()
    {
        return $this->belongsTo(User::class, 'reviewed_by_admin_id');
    }
}
