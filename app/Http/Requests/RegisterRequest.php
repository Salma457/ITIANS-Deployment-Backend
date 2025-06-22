<?php
// app/Http/Requests/RegisterRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:itian,employer',
            'certificate' => [
                // required if role is itian
                Rule::requiredIf($this->input('role') === 'itian'),
                'file',
                'mimes:pdf,jpg,png',
                'max:2048',
            ],
            'company_brief' => [
                // required if role is employer
                Rule::requiredIf($this->input('role') === 'employer'),
                'string',
                'max:1000',
            ],
        ];
    }
}
