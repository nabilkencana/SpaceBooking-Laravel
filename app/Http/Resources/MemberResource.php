<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'nama_member' => $this->nama_member,
            'instansi' => $this->instansi,
            'alamat' => $this->alamat,
            'telp' => $this->telp,
            'foto' => $this->foto,
            'foto_url' => $this->foto_url,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}