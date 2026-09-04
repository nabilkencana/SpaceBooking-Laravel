<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Guarded(['id'])]
class Reservasi extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'tanggal_reservasi' => 'date:Y-m-d',
            'jam_mulai' => 'string',
            'jam_selesai' => 'string',
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
        ];
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    public function diskon()
    {
        return $this->belongsTo(Diskon::class);
    }
}
