<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamScoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'student_id'              => 'required|exists:students,id',
            'program_id'              => 'required|exists:programs,id',
            'scores'                  => 'required|array',
            'scores.*.course_id'      => 'required|exists:courses,id',
            'scores.*.quiz_score'     => 'nullable|numeric|min:0|max:100',
            'scores.*.exam_score'     => 'nullable|numeric|min:0|max:100',
        ];
    }
}
