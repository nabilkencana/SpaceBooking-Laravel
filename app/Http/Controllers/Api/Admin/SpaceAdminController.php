<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateSpaceRequest;
use App\Http\Requests\Admin\UpdateSpaceRequest;
use App\Http\Resources\SpaceDetailResource;
use App\Http\Resources\SpaceResource;
use App\Models\Reservasi;
use App\Models\Space;
use App\Models\SpaceOwner;
use App\Traits\ApiResponse;
use App\Traits\OwnedResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SpaceAdminController extends Controller
{
    use ApiResponse, OwnedResource;

    public function index(Request $request): JsonResponse
    {
        $owner = $this->getOwner($request->user()->id);

        if (! $owner) {
            return $this->error('Data profil coworking space tidak ditemukan.', 'Not Found', 404);
        }

        $spaces = Space::where('owner_id', $owner->id)->orderBy('nama_space')->get();

        return $this->success(SpaceResource::collection($spaces));
    }

    public function store(CreateSpaceRequest $request): JsonResponse
    {
        $owner = $this->getOwner($request->user()->id);

        if (! $owner) {
            return $this->error('Data profil coworking space tidak ditemukan.', 'Not Found', 404);
        }

        $data = $request->validated();

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '-' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/spaces', $filename);
            $data['foto'] = $filename;
        }

        $space = $owner->spaces()->create($data);

        return $this->created(new SpaceDetailResource($space->load('owner')), 'Space baru berhasil ditambahkan!');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $space = $this->getOwnedSpace($request->user()->id, $id);

        if (! $space) {
            return $this->error('Space tidak ditemukan atau bukan milik Anda.', 'Not Found', 404);
        }

        return $this->success(new SpaceDetailResource($space->load('owner')));
    }

    public function update(UpdateSpaceRequest $request, int $id): JsonResponse
    {
        $space = $this->getOwnedSpace($request->user()->id, $id);

        if (! $space) {
            return $this->error('Space tidak ditemukan atau bukan milik Anda.', 'Not Found', 404);
        }

        $data = $request->validated();

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '-' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/spaces', $filename);
            $data['foto'] = $filename;
        }

        $space->update($data);

        return $this->success(new SpaceDetailResource($space->fresh('owner')), 'Data space berhasil diperbarui!');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $space = $this->getOwnedSpace($request->user()->id, $id);

        if (! $space) {
            return $this->error('Space tidak ditemukan atau bukan milik Anda.', 'Not Found', 404);
        }

        $adaReservasiAktif = Reservasi::where('space_id', $space->id)
            ->whereIn('status', ['belum_dikonfirm', 'disetujui', 'aktif'])
            ->exists();

        if ($adaReservasiAktif) {
            return $this->error(
                'Space tidak dapat dihapus karena masih memiliki reservasi aktif terkait.',
                'Bad Request',
                400
            );
        }

        $space->delete();

        return $this->success([
            'id' => (int) $id,
            'deleted' => true,
        ], 'Space berhasil dihapus!');
    }
}
