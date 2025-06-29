<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use \App\Models\JobApplication;
class Job extends Model
{
        use SoftDeletes;

    // Job Type Constants
    public const TYPE_FULL_TIME = 'Full-time';
    public const TYPE_PART_TIME = 'Part-time';
    public const TYPE_INTERNSHIP = 'Internship';
    public const TYPE_FREELANCE = 'Freelance';

    // Status Constants
    public const STATUS_OPEN = 'Open';
    public const STATUS_CLOSED = 'Closed';
    public const STATUS_PENDING = 'Pending';

    // Location Constants
    public const LOCATION_ON_SITE = 'On-site';
    public const LOCATION_REMOTE = 'Remote';
    public const LOCATION_HYBRID = 'Hybrid';

    protected $with = ['employer', 'employer.employerProfile'];


    protected $fillable = [
        'job_title',
        'employer_id',
        'description',
        'requirements',
        'qualifications',
        'job_location',
        'job_type',
        'salary_range_min',
        'salary_range_max',
        'currency',
        'posted_date',
        'application_deadline',
        'status',
        'views_count',
    ];

    protected $casts = [
        'posted_date' => 'datetime',
        'application_deadline' => 'datetime',
        'status_changed_at' => 'datetime'
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
    ];

    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function statusChanger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'status_changed_by');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public static function getJobTypes(): array
    {
        return [
            self::TYPE_FULL_TIME,
            self::TYPE_PART_TIME,
            self::TYPE_INTERNSHIP,
            self::TYPE_FREELANCE,
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_CLOSED,
            self::STATUS_PENDING,
        ];
    }

    public static function getLocations(): array
    {
        return [
            self::LOCATION_ON_SITE,
            self::LOCATION_REMOTE,
            self::LOCATION_HYBRID,
        ];
    }
}