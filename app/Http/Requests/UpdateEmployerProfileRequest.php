<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array  
    {
        return [
            'company_name' => 'sometimes|string|max:255',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'company_description' => 'nullable|string',
            'website_url' => 'nullable|url|max:500',
            'industry' => 'sometimes|string|max:255',
            'company_size' => 'sometimes|string|max:100',
            'location' => 'sometimes|string|max:255',
            'contact_person_name' => 'sometimes|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'is_verified' => 'nullable|in:true,false,1,0',
        ];
    }
}
