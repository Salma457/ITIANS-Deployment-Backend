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
            'project_title.required' => 'Project title is required',
            'project_title.string' => 'Project title must be a string',
            'project_title.max' => 'Project title must not exceed 255 characters',
            'project_title.min' => 'Project title must be at least 3 characters',
            'description.max' => 'Project description must not exceed 2000 characters',
            'project_link.url' => 'Project link must be a valid URL',
            'project_link.max' => 'Project link is too long',
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