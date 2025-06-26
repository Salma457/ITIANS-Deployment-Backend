<?php

namespace App\Http\Requests;

use App\Models\Job;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && 
               (auth()->user()->id === $this->job->employer_id || 
                auth()->user()->role === 'admin');
    }

    public function rules()
    {
        return [
            'job_title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'qualifications' => 'nullable|string',
            'status' => 'nullable|in:Open,Closed,Pending',
            'job_location' => ['nullable', Rule::in(Job::getLocations())],
            'job_type' => ['nullable', Rule::in(Job::getJobTypes())],
            'salary_range_min' => 'nullable|numeric|min:0',
            'salary_range_max' => 'nullable|numeric|min:0|gte:salary_range_min',
            'currency' => 'nullable|string|max:10',
            'application_deadline' => 'nullable|date|after:today'
        ];
    }
}