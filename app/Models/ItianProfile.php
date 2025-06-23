<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItianProfile extends Model
{
    use HasFactory;

    protected $primaryKey = 'itian_profile_id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'profile_picture',
        'bio',
        'iti_track',
        'graduation_year',
        'cv',
        'portfolio_url',
        'linkedin_profile_url',
        'github_profile_url',
        'is_open_to_work',
        'experience_years',
        'current_job_title',
        'current_company',
        'preferred_job_locations',
        'email',
        'number',
    ];

public function user()
{
    return $this->belongsTo(User::class);
}



    public function getProfilePictureUrlAttribute()
    {
        return $this->profile_picture ? asset('storage/' . $this->profile_picture) : null;
    }

    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class, 'itian_id', 'itian_profile_id');
    }

    public function projects()
    {
        return $this->hasMany(ItianProject::class, 'itian_profile_id', 'itian_profile_id');
    }

    public function skills()
    {
        return $this->hasMany(ItianSkill::class, 'itian_profile_id', 'itian_profile_id');
    }
}
