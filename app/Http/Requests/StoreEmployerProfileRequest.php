<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployerProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'company_name' => 'required|string|max:255',
            'company_logo' => 'nullable|image|max:2048',
            'company_description' => 'nullable|string',
            'website_url' => 'nullable|url|max:500',
            'industry' => 'nullable|string|max:100',
            'company_size' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:50',
        ];
    
      
        if ($this->isMethod('PATCH') || $this->isMethod('PUT')) {
            foreach ($rules as $key => &$rule) {
                if (is_string($rule)) {
                    $rule = str_replace('required', 'sometimes', $rule);
                }
            }
        }
    
        return $rules;
    }
    
}
