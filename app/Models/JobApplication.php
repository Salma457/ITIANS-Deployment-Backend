<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    //
    protected $table = 'job_application';
    protected $fillable = [
        'cv',
        'itian_id',
        'cover_letter',
        'application_date',
        'status',
        'job_id'
    ];

    function user(){
        return $this->belongsTo(User::class);
    }
    function job(){
        return $this->belongsTo(Job::class);;
    }
    function itian(){
        return $this->belongsTo(ItianProfile::class, 'itian_id','itian_profile_id');
    }

}
