<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItianProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        $rules = [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'bio' => 'nullable|string',
            'iti_track' => 'required|string|max:100',
            'graduation_year' => 'required|integer',
            'cv' => 'nullable|mimes:pdf,doc,docx|max:2048',
            'portfolio_url' => 'nullable|url|max:500',
            'linkedin_profile_url' => 'nullable|url|max:500',
            'github_profile_url' => 'nullable|url|max:500',
            'is_open_to_work' => 'required|in:true,false,1,0',
            'experience_years' => 'nullable|integer|min:0',
            'current_job_title' => 'nullable|string|max:255',
            'current_company' => 'nullable|string|max:255',
            'preferred_job_locations' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255', 
        ];
    
        return $rules;
    }
}