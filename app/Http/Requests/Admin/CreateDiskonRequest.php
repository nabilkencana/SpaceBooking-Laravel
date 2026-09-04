<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreateDiskonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_diskon' => ['required', 'string', 'max:100', 'unique:diskons,nama_diskon'],
            'persentase_diskon' => ['required', 'integer', 'between:1,100'],
            'tanggal_awal' => ['required', 'date'],
            'tanggal_akhir' => ['required', 'date', 'after:tanggal_awal'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_diskon.required' => 'Nama diskon wajib diisi',
            'nama_diskon.unique' => 'Nama diskon sudah digunakan',
            'nama_diskon.max' => 'Nama diskon maksimal :max karakter',
            'persentase_diskon.required' => 'Persentase diskon wajib diisi',
            'persentase_diskon.between' => 'Persentase diskon harus antara 1 sampai 100',
            'tanggal_awal.required' => 'Tanggal awal wajib diisi',
            'tanggal_akhir.required' => 'Tanggal akhir wajib diisi',
            'tanggal_akhir.after' => 'Tanggal akhir harus setelah tanggal awal',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_diskon' => 'Nama Diskon',
            'persentase_diskon' => 'Persentase Diskon',
            'tanggal_awal' => 'Tanggal Awal',
            'tanggal_akhir' => 'Tanggal Akhir',
        ];
    }
}
