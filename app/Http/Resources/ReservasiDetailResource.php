<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservasiDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode_booking' => $this->kode_booking,
            'id_member' => $this->member_id,
            'id_space' => $this->space_id,
            'id_diskon' => $this->diskon_id,
            'tanggal_reservasi' => $this->tanggal_reservasi?->format('Y-m-d'),
            'jam_mulai' => $this->jam_mulai,
            'jam_selesai' => $this->jam_selesai,
            'durasi_jam' => $this->durasi_jam,
            'harga_per_jam' => $this->harga_per_jam,
            'total_harga_awal' => $this->total_harga_awal,
            'potongan_diskon' => $this->potongan_diskon,
            'total_bayar' => $this->total_bayar,
            'status' => $this->status,
            'check_in_at' => $this->check_in_at?->toIso8601String(),
            'check_out_at' => $this->check_out_at?->toIso8601String(),
            'member' => $this->whenLoaded('member', function () {
                return [
                    'id' => $this->member->id,
                    'nama_member' => $this->member->nama_member,
                    'instansi' => $this->member->instansi,
                    'telp' => $this->member->telp,
                ];
            }),
            'space' => $this->whenLoaded('space', function () {
                return [
                    'id' => $this->space->id,
                    'nama_space' => $this->space->nama_space,
                    'harga_per_jam' => $this->space->harga_per_jam,
                    'tipe' => $this->space->tipe,
                    'foto_url' => $this->space->foto_url,
                ];
            }),
            'diskon' => $this->whenLoaded('diskon', function () {
                return $this->diskon
                    ? [
                        'id' => $this->diskon->id,
                        'nama_diskon' => $this->diskon->nama_diskon,
                        'persentase_diskon' => $this->diskon->persentase_diskon,
                    ]
                    : null;
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}