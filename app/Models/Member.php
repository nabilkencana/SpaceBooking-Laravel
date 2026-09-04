<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Guarded(['id'])]
class Member extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reservasis()
    {
        return $this->hasMany(Reservasi::class);
    }

    public function getFotoUrlAttribute()
    {
        return $this->foto ? Storage::url('members/' . $this->foto) : null;
    }
}
