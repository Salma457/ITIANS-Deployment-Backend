<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItianProjectRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'project_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_link' => 'nullable|url',
        ];
    }
}

