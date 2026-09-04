<?php

namespace App\Http\Requests\Admin;

use App\Models\Member;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $memberId = $this->route('id');
        $member = Member::find($memberId);
        $userId = $member?->user_id;

        return [
            'username' => ['sometimes', 'string', 'min:3', 'max:50', 'unique:users,username,' . $userId],
            'password' => ['sometimes', 'nullable', 'string', 'min:6'],
            'nama_member' => ['sometimes', 'string', 'max:100'],
            'instansi' => ['sometimes', 'string', 'max:100'],
            'alamat' => ['sometimes', 'string'],
            'telp' => ['sometimes', 'string', 'max:20'],
            'foto' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.min' => 'Username minimal :min karakter',
            'username.unique' => 'Username sudah digunakan oleh akun lain',
            'password.min' => 'Password minimal :min karakter',
            'nama_member.max' => 'Nama member maksimal :max karakter',
            'instansi.max' => 'Instansi maksimal :max karakter',
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
