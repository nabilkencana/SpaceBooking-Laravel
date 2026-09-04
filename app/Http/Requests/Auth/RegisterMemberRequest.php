<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterMemberRequest extends FormRequest
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
            'nama_member' => ['required', 'string', 'max:100'],
            'instansi' => ['required', 'string', 'max:100'],
            'alamat' => ['required', 'string'],
            'telp' => ['required', 'string', 'max:20'],
            'foto' => ['nullable', 'string', 'max:255'],
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
            'nama_member.required' => 'Nama member wajib diisi',
            'nama_member.max' => 'Nama member maksimal :max karakter',
            'instansi.required' => 'Instansi wajib diisi',
            'alamat.required' => 'Alamat wajib diisi',
            'telp.required' => 'Nomor telepon wajib diisi',
            'telp.max' => 'Nomor telepon maksimal :max karakter',
        ];
    }

    public function attributes(): array
    {
        return [
            'username' => 'Username',
            'password' => 'Password',
            'nama_member' => 'Nama Member',
            'instansi' => 'Instansi',
            'alamat' => 'Alamat',
            'telp' => 'Nomor Telepon',
            'foto' => 'Foto',
        ];
    }
}
