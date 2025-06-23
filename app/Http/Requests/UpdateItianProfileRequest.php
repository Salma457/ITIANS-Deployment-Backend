<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItianProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'profile_picture' => 'nullable|image|max:2048',
            'bio' => 'nullable|string',
            'iti_track' => 'sometimes|string|max:100',
            'graduation_year' => 'sometimes|integer',
            'cv' => 'nullable|mimes:pdf,doc,docx|max:4096',
            'portfolio_url' => 'nullable|url|max:500',
            'linkedin_profile_url' => 'nullable|url|max:500',
            'github_profile_url' => 'nullable|url|max:500',
            'is_open_to_work' => 'nullable|boolean',
            'experience_years' => 'nullable|integer|min:0',
            'current_job_title' => 'nullable|string|max:255',
            'current_company' => 'nullable|string|max:255',
            'preferred_job_locations' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'number' => 'nullable|string|max:255',
        ];
    }
}