<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'student_id'   => 'required|exists:students,id',
            'amount_paid'  => 'required|numeric|gt:0',
            'payment_date' => 'required|date',
            'notes'        => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'amount_paid.gt' => 'Amount paid must be greater than zero.',
        ];
    }
}
