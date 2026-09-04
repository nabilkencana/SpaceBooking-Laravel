<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDiskonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $diskonId = $this->route('id');

        return [
            'nama_diskon' => ['sometimes', 'string', 'max:100', 'unique:diskons,nama_diskon,' . $diskonId],
            'persentase_diskon' => ['sometimes', 'integer', 'between:1,100'],
            'tanggal_awal' => ['sometimes', 'date'],
            'tanggal_akhir' => ['sometimes', 'date', 'after:tanggal_awal'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_diskon.unique' => 'Nama diskon sudah digunakan',
            'nama_diskon.max' => 'Nama diskon maksimal :max karakter',
            'persentase_diskon.between' => 'Persentase diskon harus antara 1 sampai 100',
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
