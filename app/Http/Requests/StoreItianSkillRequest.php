<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItianSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'skill_name' => 'required|string|max:100|min:2',
        ];
    }

    public function messages(): array
    {
        return [
            'skill_name.required' => 'اسم المهارة مطلوب',
            'skill_name.string' => 'اسم المهارة يجب أن يكون نص',
            'skill_name.max' => 'اسم المهارة يجب ألا يزيد عن 100 حرف',
            'skill_name.min' => 'اسم المهارة يجب ألا يقل عن حرفين',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'skill_name' => trim($this->skill_name),
        ]);
    }
}