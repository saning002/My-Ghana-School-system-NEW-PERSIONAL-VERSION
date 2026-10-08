<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'date_of_birth' => 'required|date',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'student_id' => 'required|string|unique:students,student_id',
            'admission_date' => 'required|date',
            'program_id' => 'required|exists:programs,id',
            'level' => 'required|integer|min:1|max:4',
            'church_branch_id' => 'required|exists:church_branches,id',
        ];
    }
}
