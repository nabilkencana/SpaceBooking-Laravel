<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreateSpaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_space' => ['required', 'string', 'max:100'],
            'harga_per_jam' => ['required', 'integer', 'min:0'],
            'tipe' => ['required', 'string', 'in:desk,meeting_room,private_office'],
            'kapasitas' => ['required', 'integer', 'min:1'],
            'deskripsi' => ['required', 'string'],
            'foto' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_space.required' => 'Nama space wajib diisi',
            'nama_space.max' => 'Nama space maksimal :max karakter',
            'harga_per_jam.required' => 'Harga per jam wajib diisi',
            'harga_per_jam.integer' => 'Harga per jam harus berupa angka',
            'harga_per_jam.min' => 'Harga per jam tidak boleh negatif',
            'tipe.required' => 'Tipe space wajib dipilih',
            'tipe.in' => 'Tipe space harus salah satu dari: desk, meeting_room, private_office',
            'kapasitas.required' => 'Kapasitas wajib diisi',
            'kapasitas.min' => 'Kapasitas minimal 1 orang',
            'deskripsi.required' => 'Deskripsi wajib diisi',
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
