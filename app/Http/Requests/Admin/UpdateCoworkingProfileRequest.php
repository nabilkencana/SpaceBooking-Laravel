<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCoworkingProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_coworking' => ['sometimes', 'string', 'max:100'],
            'nama_pemilik' => ['sometimes', 'string', 'max:100'],
            'telp' => ['sometimes', 'string', 'max:20'],
            'alamat' => ['sometimes', 'nullable', 'string'],
            'deskripsi' => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_coworking.max' => 'Nama coworking space maksimal :max karakter',
            'nama_pemilik.max' => 'Nama pemilik maksimal :max karakter',
            'telp.max' => 'Nomor telepon maksimal :max karakter',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_coworking' => 'Nama Coworking Space',
            'nama_pemilik' => 'Nama Pemilik',
            'telp' => 'Nomor Telepon',
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi',
        ];
    }
}
