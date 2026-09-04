<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiskonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $now = Carbon::now();

        return [
            'id' => $this->id,
            'nama_diskon' => $this->nama_diskon,
            'persentase_diskon' => $this->persentase_diskon,
            'tanggal_awal' => $this->tanggal_awal?->toIso8601String(),
            'tanggal_akhir' => $this->tanggal_akhir?->toIso8601String(),
            'is_active' => $this->tanggal_awal && $this->tanggal_akhir
                ? $now->between($this->tanggal_awal, $this->tanggal_akhir)
                : false,
        ];
    }
}