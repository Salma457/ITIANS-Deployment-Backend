<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobReviews extends Model
{
    protected $fillable =[
        'reviewer_id',
        'job_id',
        'content',
    ];

    function job(){
        return $this->belongsTo(Job::class);
    }
    function Reviewer(){
        return $this->belongsTo(User::class);
    }
}
