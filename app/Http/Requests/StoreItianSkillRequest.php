<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItianSkillRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'skill_name' => 'required|string|max:100',
        ];
    }
}

