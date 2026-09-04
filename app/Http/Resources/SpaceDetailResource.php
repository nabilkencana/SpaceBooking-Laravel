<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpaceDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_owner' => $this->owner_id,
            'nama_space' => $this->nama_space,
            'harga_per_jam' => $this->harga_per_jam,
            'tipe' => $this->tipe,
            'kapasitas' => $this->kapasitas,
            'deskripsi' => $this->deskripsi,
            'foto' => $this->foto,
            'foto_url' => $this->foto_url,
            'owner' => $this->whenLoaded('owner', function () {
                return [
                    'id' => $this->owner->id,
                    'nama_coworking' => $this->owner->nama_coworking,
                    'nama_pemilik' => $this->owner->nama_pemilik,
                    'telp' => $this->owner->telp,
                    'alamat' => $this->owner->alamat,
                    'deskripsi' => $this->owner->deskripsi,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}