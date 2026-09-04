<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCoworkingProfileRequest;
use App\Http\Resources\SpaceOwnerResource;
use App\Models\SpaceOwner;
use App\Traits\ApiResponse;
use App\Traits\OwnedResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponse, OwnedResource;

    public function show(Request $request): JsonResponse
    {
        $owner = $this->getOwner($request->user()->id);

        if (! $owner) {
            return $this->error('Data profil coworking space tidak ditemukan.', 'Not Found', 404);
        }

        return $this->success(new SpaceOwnerResource($owner));
    }

    public function update(UpdateCoworkingProfileRequest $request): JsonResponse
    {
        $owner = $this->getOwner($request->user()->id);

        if (! $owner) {
            return $this->error('Data profil coworking space tidak ditemukan.', 'Not Found', 404);
        }

        $owner->update($request->validated());

        return $this->success(
            new SpaceOwnerResource($owner->fresh()),
            'Profil Coworking Space berhasil diperbarui!'
        );
    }
}
