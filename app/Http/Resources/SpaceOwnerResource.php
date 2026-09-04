<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpaceOwnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'nama_coworking' => $this->nama_coworking,
            'nama_pemilik' => $this->nama_pemilik,
            'telp' => $this->telp,
            'alamat' => $this->alamat,
            'deskripsi' => $this->deskripsi,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}