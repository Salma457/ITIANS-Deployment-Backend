<?php

namespace App\Http\Requests;

use App\Models\Job;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->role === 'employer';
    }

    public function rules()
    {
        return [
            'job_title' => 'required|string|max:255',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'qualifications' => 'nullable|string',
            'job_location' => ['required', Rule::in(Job::getLocations())],
            'job_type' => ['required', Rule::in(Job::getJobTypes())],
            'salary_range_min' => 'nullable|numeric|min:0',
            'salary_range_max' => 'nullable|numeric|min:0|gte:salary_range_min',
            'currency' => 'nullable|string|size:3',
            'application_deadline' => 'nullable|date|after:today'
        ];
    }
}