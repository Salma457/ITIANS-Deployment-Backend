<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployerProfile extends Model
{
    use HasFactory;

    protected $primaryKey = 'employer_profile_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'company_name',
        'company_logo',
        'company_description',
        'website_url',
        'industry',
        'company_size',
        'location',
        'contact_person_name',
        'contact_email',
        'phone_number',
        'is_verified',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCompanyLogoUrlAttribute()
    {
        return $this->company_logo ? asset('storage/' . $this->company_logo) : null;
    }
}
