<?php

namespace App\Traits;

use App\Models\Reservasi;
use App\Models\Space;
use App\Models\SpaceOwner;

trait OwnedResource
{
    private function getOwner(int $userId): ?SpaceOwner
    {
        return SpaceOwner::where('user_id', $userId)->first();
    }

    private function getOwnedSpace(int $userId, int $spaceId): ?Space
    {
        return $this->getOwner($userId)?->spaces()->find($spaceId);
    }

    private function getOwnedReservasi(int $userId, int $reservasiId): ?Reservasi
    {
        return ($owner = $this->getOwner($userId))
            ? Reservasi::whereIn('space_id', $owner->spaces()->pluck('id'))->find($reservasiId)
            : null;
    }
}
