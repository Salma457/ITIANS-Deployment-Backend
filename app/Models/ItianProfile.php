<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ItianProfile;
class ItianProfile extends Model
{
      protected $primaryKey = 'itian_profile_id'; // اسم العمود الأساسي

    public $incrementing = true; // إذا كنت تستخدم AUTO_INCREMENT
    protected $keyType = 'int'; // أو 'string' إذا كان UUID

    protected $fillable = [
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
        'user_id',
    ];
    public function user()
{
    return $this->belongsTo(User::class);
}

}
