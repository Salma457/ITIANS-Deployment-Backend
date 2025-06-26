<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'company_name' =>$this->employer->employerProfile->company_name ?? null,
            'job_title' => $this->job_title,
            'description' => $this->description,
            'requirements' => $this->requirements,
            'qualifications' => $this->qualifications,
            'job_location' => $this->job_location,
            'job_type' => $this->job_type,
            'applications_count' => $this->applications_count,
            'salary_range' => [
                'min' => $this->salary_range_min,
                'max' => $this->salary_range_max,
                'currency' => $this->currency,
            ],
            'posted_date' => $this->posted_date,
            'application_deadline' => $this->application_deadline,
            'status' => $this->status,
            'views_count' => $this->views_count,
            'employer' => [
                'id' => $this->employer->id,
                'name' => $this->employer->name,
                // Add other employer fields as needed
            ],

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}