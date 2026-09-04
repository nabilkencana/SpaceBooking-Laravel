<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSpaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_space' => ['sometimes', 'string', 'max:100'],
            'harga_per_jam' => ['sometimes', 'integer', 'min:0'],
            'tipe' => ['sometimes', 'string', 'in:desk,meeting_room,private_office'],
            'kapasitas' => ['sometimes', 'integer', 'min:1'],
            'deskripsi' => ['sometimes', 'string'],
            'foto' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_space.max' => 'Nama space maksimal :max karakter',
            'harga_per_jam.integer' => 'Harga per jam harus berupa angka',
            'harga_per_jam.min' => 'Harga per jam tidak boleh negatif',
            'tipe.in' => 'Tipe space harus salah satu dari: desk, meeting_room, private_office',
            'kapasitas.min' => 'Kapasitas minimal 1 orang',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_space' => 'Nama Space',
            'harga_per_jam' => 'Harga per Jam',
            'tipe' => 'Tipe Space',
            'kapasitas' => 'Kapasitas',
            'deskripsi' => 'Deskripsi',
            'foto' => 'Foto',
        ];
    }
}
