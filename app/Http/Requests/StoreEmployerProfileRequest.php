<?php
// StoreEmployerProfileRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', 
            'company_description' => 'nullable|string',
            'website_url' => 'nullable|url|max:500',
            'industry' => 'nullable|string|max:255',
            'company_size' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'is_verified' => 'nullable|boolean', 
        ];
    }

    public function messages()
    {
        return [
            'company_logo.image' => 'The company logo must be an image file.',
            'company_logo.mimes' => 'The company logo must be a file of type: jpeg, png, jpg, gif.',
            'company_logo.max' => 'The company logo size must not exceed 5MB.',
        ];
    }
}

// UpdateEmployerProfileRequest.php


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
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', 
            'company_description' => 'nullable|string',
            'website_url' => 'nullable|url|max:500',
            'industry' => 'sometimes|string|max:255',
            'company_size' => 'sometimes|string|max:100',
            'location' => 'sometimes|string|max:255',
            'contact_person_name' => 'sometimes|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'is_verified' => 'nullable|boolean',
            'company_logo_removed' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'company_logo.image' => 'The company logo must be an image file.',
            'company_logo.mimes' => 'The company logo must be a file of type: jpeg, png, jpg, gif.',
            'company_logo.max' => 'The company logo size must not exceed 5MB.',
        ];
    }
}