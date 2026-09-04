<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservasiStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:disetujui,dibatalkan'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status wajib diisi',
            'status.in' => 'Status harus salah satu dari: disetujui, dibatalkan',
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => 'Status',
        ];
    }
}
