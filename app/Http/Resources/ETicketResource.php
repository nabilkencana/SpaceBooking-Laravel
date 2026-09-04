<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ETicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $tipeLabels = [
            'desk' => 'Personal Desk',
            'meeting_room' => 'Meeting Room',
            'private_office' => 'Private Office',
        ];

        $this->loadMissing(['member.user', 'space.owner', 'diskon']);

        $diskonInfo = $this->diskon
            ? sprintf('%d%% (%s)', $this->diskon->persentase_diskon, $this->diskon->nama_diskon)
            : null;

        return [
            'e_ticket_number' => sprintf(
                'TICKET-%s-%04d',
                $this->tanggal_reservasi?->format('Ymd'),
                $this->id
            ),
            'kode_booking' => $this->kode_booking,
            'coworking_space' => $this->space && $this->space->owner
                ? [
                    'nama' => $this->space->owner->nama_coworking,
                    'telepon' => $this->space->owner->telp,
                    'alamat' => $this->space->owner->alamat,
                ]
                : null,
            'member' => $this->member
                ? [
                    'nama' => $this->member->nama_member,
                    'instansi' => $this->member->instansi,
                    'telp' => $this->member->telp,
                ]
                : null,
            'space' => $this->space
                ? [
                    'nama' => $this->space->nama_space,
                    'tipe' => $tipeLabels[$this->space->tipe] ?? $this->space->tipe,
                    'harga_per_jam' => $this->space->harga_per_jam,
                ]
                : null,
            'jadwal' => [
                'tanggal' => $this->tanggal_reservasi?->format('Y-m-d'),
                'jam_mulai' => $this->jam_mulai,
                'jam_selesai' => $this->jam_selesai,
                'durasi' => $this->durasi_jam . ' Jam',
            ],
            'rincian_pembayaran' => [
                'tarif_kotor' => $this->total_harga_awal,
                'diskon_promo' => $diskonInfo,
                'potongan' => $this->potongan_diskon,
                'total_dibayar' => $this->total_bayar,
            ],
            'status_reservasi' => $this->status,
            'qr_code_payload' => sprintf('VERIFY-RESERVASI-%d-%s', $this->id, $this->kode_booking),
            'check_in_at' => $this->check_in_at?->toIso8601String(),
            'check_out_at' => $this->check_out_at?->toIso8601String(),
        ];
    }
}