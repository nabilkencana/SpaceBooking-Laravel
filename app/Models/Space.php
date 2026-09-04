<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Guarded(['id'])]
class Space extends Model
{
    use HasFactory;

    public function owner()
    {
        return $this->belongsTo(SpaceOwner::class, 'owner_id');
    }

    public function reservasis()
    {
        return $this->hasMany(Reservasi::class);
    }

    public function getFotoUrlAttribute()
    {
        return $this->foto ? Storage::url('spaces/' . $this->foto) : null;
    }
}
