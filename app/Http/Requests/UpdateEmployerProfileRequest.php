<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateEmployerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only authenticated users can update their profile
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            // 'sometimes' means the field is not required, but if present, it must follow the rules.
            'company_name' => 'sometimes|string|max:255',
            'company_description' => 'nullable|string',
            'website_url' => 'nullable|url|max:500',
            'industry' => 'nullable|string|max:100',
            'company_size' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'contact_person_name' => 'sometimes|string|max:100',
            'contact_email' => 'sometimes|email|max:100',
            'phone_number' => 'nullable|string|max:20',
            // For company_logo:
            // 'nullable' allows it to be empty (e.g., if user removes it)
            // 'image|mimes:...' validates if a file is provided and it's an image
            'company_logo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ];
    }

    public function prepareForValidation()
    {
        $fieldsToClean = [
            'company_name',
            'company_description',
            'website_url',
            'industry',
            'company_size',
            'location',
            'contact_person_name',
            'contact_email',
            'phone_number'
        ];

        foreach ($fieldsToClean as $field) {
            if ($this->has($field)) {
                $this->merge([
                    $field => stripslashes(trim($this->input($field), "\"' "))
                ]);
            }
        }
    }
}