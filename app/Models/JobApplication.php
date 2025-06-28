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
        'status',
        'job_id',
    ];

    function user(){
        return $this->belongsTo(User::class);
    }
    function job(){
        return $this->belongsTo(Job::class);;
    }
    public function itian()
    {
        return $this->belongsTo(ItianProfile::class, 'itian_id');
    }


    function employer(){
        return $this->belongsTo(EmployerProfile::class,'employer_id','employer_profile_id');
    }

}
