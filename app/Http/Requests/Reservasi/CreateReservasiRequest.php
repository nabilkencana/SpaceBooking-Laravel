<?php

namespace App\Http\Requests\Reservasi;

use Illuminate\Foundation\Http\FormRequest;

class CreateReservasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_space' => ['required', 'integer', 'exists:spaces,id'],
            'tanggal_reservasi' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:today'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'durasi_jam' => ['required', 'integer', 'min:1', 'max:24'],
            'id_diskon' => ['nullable', 'integer', 'exists:diskons,id'],
            'kode_promo' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_space.required' => 'Space wajib dipilih',
            'id_space.exists' => 'Space tidak ditemukan',
            'tanggal_reservasi.required' => 'Tanggal reservasi wajib diisi',
            'tanggal_reservasi.after_or_equal' => 'Tanggal reservasi tidak boleh di masa lalu',
            'jam_mulai.required' => 'Jam mulai wajib diisi',
            'jam_mulai.date_format' => 'Format jam mulai harus HH:mm (contoh: 09:00)',
            'durasi_jam.required' => 'Durasi jam wajib diisi',
            'durasi_jam.min' => 'Durasi minimal 1 jam',
            'durasi_jam.max' => 'Durasi maksimal 24 jam',
            'id_diskon.exists' => 'Diskon tidak ditemukan',
            'kode_promo.max' => 'Kode promo maksimal :max karakter',
        ];
    }

    public function attributes(): array
    {
        return [
            'id_space' => 'Space',
            'tanggal_reservasi' => 'Tanggal Reservasi',
            'jam_mulai' => 'Jam Mulai',
            'durasi_jam' => 'Durasi Jam',
            'id_diskon' => 'Diskon',
            'kode_promo' => 'Kode Promo',
        ];
    }
}
