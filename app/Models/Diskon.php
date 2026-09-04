<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Guarded(['id'])]
class Diskon extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'tanggal_awal' => 'datetime',
            'tanggal_akhir' => 'datetime',
        ];
    }

    public function reservasis()
    {
        return $this->hasMany(Reservasi::class);
    }
}
