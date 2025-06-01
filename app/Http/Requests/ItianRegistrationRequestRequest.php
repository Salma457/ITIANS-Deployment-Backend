<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItianRegistrationRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'itian'; // Only itians can submit
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }
}
