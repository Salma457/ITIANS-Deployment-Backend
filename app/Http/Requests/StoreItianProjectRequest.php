<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItianProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_title' => 'required|string|max:255|min:3',
            'description' => 'nullable|string|max:2000',
            'project_link' => 'nullable|url|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'project_title.required' => 'عنوان المشروع مطلوب',
            'project_title.string' => 'عنوان المشروع يجب أن يكون نص',
            'project_title.max' => 'عنوان المشروع يجب ألا يزيد عن 255 حرف',
            'project_title.min' => 'عنوان المشروع يجب ألا يقل عن 3 أحرف',
            'description.max' => 'وصف المشروع يجب ألا يزيد عن 2000 حرف',
            'project_link.url' => 'رابط المشروع غير صحيح',
            'project_link.max' => 'رابط المشروع طويل جداً',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'project_title' => trim($this->project_title),
            'description' => $this->description ? trim($this->description) : null,
        ]);
    }
}