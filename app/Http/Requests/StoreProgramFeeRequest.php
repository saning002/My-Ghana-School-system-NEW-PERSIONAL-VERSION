<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProgramFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'program_id' => 'required|exists:programs,id',
            'amount'     => 'required|numeric|min:0',
            'exam_fee'   => 'nullable|numeric|min:0',
        ];
    }
}
