<?php

namespace App\Http\Requests;

use App\Models\Job;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobStatusRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && 
               (auth()->user()->id === $this->job->employer_id || 
                auth()->user()->role === 'admin');
    }

    public function rules()
    {
        $allowedStatuses = $this->job->employer_id === auth()->id()
            ? [Job::STATUS_OPEN, Job::STATUS_CLOSED] // Employer can only set these
            : Job::getStatuses(); 
        return [
            'status' => ['required', Rule::in($allowedStatuses)],
            'reason' => 'nullable|string|max:500'
        ];
    }
}