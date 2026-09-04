<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Diskon\CheckPromoRequest;
use App\Http\Resources\DiskonResource;
use App\Models\Diskon;
use App\Services\DiskonService;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class DiskonController extends Controller
{
    use ApiResponse;

    public function __construct(protected DiskonService $diskonService)
    {
    }

    public function active(): JsonResponse
    {
        $now = Carbon::now();

        $diskons = Diskon::where('tanggal_awal', '<=', $now)
            ->where('tanggal_akhir', '>=', $now)
            ->orderBy('tanggal_akhir')
            ->get();

        return $this->success(DiskonResource::collection($diskons));
    }

    public function check(CheckPromoRequest $request): JsonResponse
    {
        try {
            $diskon = $this->diskonService->validasiKodePromo($request->nama_diskon);

            return $this->success(
                (new DiskonResource($diskon))->additional(['is_active' => true]),
                'Kode promo valid dan masih berlaku!'
            );
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 'Bad Request', 400);
        }
    }

    public function show(int $id): JsonResponse
    {
        $diskon = Diskon::find($id);

        if (! $diskon) {
            return $this->error('Data diskon tidak ditemukan!', 'Not Found', 404);
        }

        return $this->success(new DiskonResource($diskon));
    }
}
