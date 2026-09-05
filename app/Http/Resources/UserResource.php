<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'role' => $this->role,
            'member' => $this->role === 'member' && $this->relationLoaded('member') && $this->member
                ? new MemberResource($this->member)
                : null,
            'space_owner' => $this->role === 'admin_space' && $this->relationLoaded('spaceOwner') && $this->spaceOwner
                ? new SpaceOwnerResource($this->spaceOwner)
                : null,
        ];
    }
}