<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateDiskonRequest;
use App\Http\Requests\Admin\UpdateDiskonRequest;
use App\Http\Resources\DiskonResource;
use App\Models\Diskon;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class DiskonAdminController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $diskons = Diskon::orderByDesc('created_at')->get();

        return $this->success(DiskonResource::collection($diskons));
    }

    public function store(CreateDiskonRequest $request): JsonResponse
    {
        $diskon = Diskon::create($request->validated());

        return $this->created(new DiskonResource($diskon), 'Kode promo baru berhasil dibuat!');
    }

    public function show(int $id): JsonResponse
    {
        $diskon = Diskon::find($id);

        if (! $diskon) {
            return $this->error('Data promo diskon tidak ditemukan!', 'Not Found', 404);
        }

        return $this->success(new DiskonResource($diskon));
    }

    public function update(UpdateDiskonRequest $request, int $id): JsonResponse
    {
        $diskon = Diskon::find($id);

        if (! $diskon) {
            return $this->error('Data promo diskon tidak ditemukan!', 'Not Found', 404);
        }

        $diskon->update($request->validated());

        return $this->success(new DiskonResource($diskon->fresh()), 'Data promo diskon berhasil diperbarui!');
    }

    public function destroy(int $id): JsonResponse
    {
        $diskon = Diskon::find($id);

        if (! $diskon) {
            return $this->error('Data promo diskon tidak ditemukan!', 'Not Found', 404);
        }

        $diskon->delete();

        return $this->success([
            'id' => (int) $id,
            'deleted' => true,
        ], 'Kode promo berhasil dihapus!');
    }
}
