<?php

namespace App\Http\Requests\Diskon;

use Illuminate\Foundation\Http\FormRequest;

class CheckPromoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_diskon' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_diskon.required' => 'Kode promo wajib diisi',
            'nama_diskon.max' => 'Kode promo maksimal :max karakter',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_diskon' => 'Kode Promo',
        ];
    }
}
