<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'username' => $this->username,
            'role' => $this->role,
        ];

        if ($this->role === 'member') {
            $data['member'] = $this->relationLoaded('member') && $this->member
                ? new MemberResource($this->member)
                : null;
            $data['space_owner'] = null;
        } elseif ($this->role === 'admin_space') {
            $data['member'] = null;
            $data['space_owner'] = $this->relationLoaded('spaceOwner') && $this->spaceOwner
                ? new SpaceOwnerResource($this->spaceOwner)
                : null;
        } else {
            $data['member'] = null;
            $data['space_owner'] = null;
        }

        return $data;
    }
}