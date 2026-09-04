<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterAdminSpaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'min:3', 'max:50', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6'],
            'nama_coworking' => ['required', 'string', 'max:100'],
            'nama_pemilik' => ['required', 'string', 'max:100'],
            'telp' => ['required', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah digunakan oleh akun lain',
            'username.min' => 'Username minimal :min karakter',
            'username.max' => 'Username maksimal :max karakter',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal :min karakter',
            'nama_coworking.required' => 'Nama coworking space wajib diisi',
            'nama_coworking.max' => 'Nama coworking space maksimal :max karakter',
            'nama_pemilik.required' => 'Nama pemilik wajib diisi',
            'nama_pemilik.max' => 'Nama pemilik maksimal :max karakter',
            'telp.required' => 'Nomor telepon wajib diisi',
            'telp.max' => 'Nomor telepon maksimal :max karakter',
        ];
    }

    public function attributes(): array
    {
        return [
            'username' => 'Username',
            'password' => 'Password',
            'nama_coworking' => 'Nama Coworking Space',
            'nama_pemilik' => 'Nama Pemilik',
            'telp' => 'Nomor Telepon',
        ];
    }
}
